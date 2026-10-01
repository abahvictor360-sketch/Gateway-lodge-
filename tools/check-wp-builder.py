#!/usr/bin/env python3
"""Checks on the WordPress side of the build, for faults that already shipped.

The Elementor builder runs on the server, so most of its faults are only
visible in a rendered page. Two are visible from here, and both cost a cycle:

  * a generated PHP file edited by hand, or a generator that was never re-run,
    so the WordPress build and the static pages disagree about the copy;
  * a container's CSS classes written under the widget's control name, which
    Elementor saves without complaint and renders as nothing at all.

Run from the repo root:  python3 tools/check-wp-builder.py
Exits non-zero if anything is wrong, so it can gate a deploy.
"""

import hashlib
import os
import re
import subprocess
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SANDBOX = os.path.join(ROOT, "wordpress", "novamira-sandbox")

GENERATORS = {
    "tools/build-wp-property-content.py": "gwl-property-content.php",
    "tools/build-wp-footers.py": "gwl-property-footers.php",
}


def digest(path):
    with open(path, "rb") as f:
        return hashlib.sha256(f.read()).hexdigest()


def check_generated_in_sync():
    """Re-run each generator and see whether its output actually changes.

    `tools/build-landings.py` holds PROPERTIES, the single source of truth. The
    PHP the builder consumes is derived from it, and the header of each
    generated file says not to hand-edit - which is exactly the kind of notice
    that gets read after the fact.
    """
    problems = []
    for script, generated in GENERATORS.items():
        target = os.path.join(SANDBOX, generated)
        if not os.path.exists(target):
            problems.append(f"{generated} is missing; run {script}")
            continue
        before = digest(target)
        run = subprocess.run([sys.executable, os.path.join(ROOT, script)],
                             cwd=ROOT, capture_output=True, text=True)
        if run.returncode != 0:
            problems.append(f"{script} failed: {run.stderr.strip().splitlines()[-1:]}")
            continue
        if digest(target) != before:
            problems.append(f"{generated} was out of date with PROPERTIES; "
                            f"{script} has just regenerated it - commit the result")
    return problems


def check_container_class_key():
    """A container reads `css_classes`; only a widget reads `_css_classes`.

    Writing the widget key on a container saves cleanly and renders no class,
    so a stylesheet rule written against it silently matches nothing. That is
    how the hero's gradient stayed invisible under the background video.
    """
    path = os.path.join(SANDBOX, "gwl-builder.php")
    if not os.path.exists(path):
        return [f"{path} is missing"]
    src = open(path, encoding="utf-8").read()
    body = re.search(r"function gwl_container\(.*?\n\}", src, re.S)
    if not body:
        return ["could not find gwl_container() in gwl-builder.php"]
    if "'_css_classes'" in body.group(0):
        return ["gwl_container() writes '_css_classes'; a container's control "
                "is 'css_classes' and the underscored key renders nothing"]
    return []


def main():
    problems = check_generated_in_sync() + check_container_class_key()
    for problem in problems:
        print("FAIL  " + problem)
    if not problems:
        print("wordpress build checks pass")
    return 1 if problems else 0


if __name__ == "__main__":
    sys.exit(main())
