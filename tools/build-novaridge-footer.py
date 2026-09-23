#!/usr/bin/env python3
"""Derive the Nova Ridge footer from the live group-site footer.

The group site's footer (wordpress/templates/footer-11063.json) is the approved
design. This keeps every style setting in it untouched and swaps only the
content: the link columns, the descriptive paragraph and the bottom bar. That
way the subdomain footer is the group footer, not an approximation of it.

Output: wordpress/templates/novaridge-footer.json, which the WordPress build
saves as the XPRO footer template.

Run from the repo root:  python3 tools/build-novaridge-footer.py
"""

import hashlib
import json
import os

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, "wordpress", "templates", "footer-11063.json")
OUT = os.path.join(ROOT, "wordpress", "templates", "novaridge-footer.json")
OUT_PHP = os.path.join(ROOT, "wordpress", "novamira-sandbox", "gwl-nr-footer-data.php")

GROUP = "https://www.gatewaylodgegroup.com"
SITE = "https://novaridge.gatewaylodgegroup.com"
PHONE = "+233 24 000 0000"
TEL = "tel:+233240000000"
WHATSAPP = "https://wa.me/233240000000"
EMAIL = "reservations@gatewaylodgegroup.com"

# Ids have to be unique per document; derive stable new ones from the old.
_seen = set()


def new_id(old):
    for salt in range(100):
        h = hashlib.md5(f"novaridge-footer:{old}:{salt}".encode()).hexdigest()[:7]
        if h not in _seen:
            _seen.add(h)
            return h
    raise RuntimeError("could not allocate id")


def link(url, external=""):
    return {"url": url, "is_external": external, "nofollow": ""}


# The three link columns, in the order the group footer lays them out.
COLUMNS = [
    ("Nova Ridge", [
        ("Home", SITE + "/"),
        ("About", SITE + "/about/"),
        ("Facilities", SITE + "/facilities/"),
        ("Contact", SITE + "/contact/"),
    ]),
    ("Gateway Lodge Group", [
        ("Group Website", GROUP + "/"),
        ("Our Properties", GROUP + "/properties/"),
        ("Offers", GROUP + "/offers/"),
        ("About Us", GROUP + "/about-us/"),
    ]),
    ("Reservations", [
        (PHONE, TEL),
        ("WhatsApp", WHATSAPP),
        (EMAIL, "mailto:" + EMAIL),
    ]),
]

INTRO = ("<p>Gateway Nova Ridge is a private serviced apartment in Ridge, Accra, and part of "
         "Gateway Lodge Group, a growing collection of hospitality destinations across Ghana.</p>")

BOTTOM_LINKS = ('<p><a style="color:rgba(255,255,255,0.55)" href="{g}/privacy-policy-2/">Privacy Policy</a>'
                ' &nbsp;&nbsp; <a style="color:rgba(255,255,255,0.55)" href="{g}/terms/">'
                'Terms &amp; Conditions</a></p>').format(g=GROUP)


def transform(elements, state):
    """Walk the tree, renaming ids and replacing content in place."""
    for el in elements:
        if "id" in el:
            el["id"] = new_id(el["id"])
        s = el.setdefault("settings", {})
        w = el.get("widgetType")

        if w == "heading" and s.get("title") == "Gateway Lodge Group":
            # The brand lockup beside the logo stays the group's, and links home.
            s["link"] = link(SITE + "/")

        elif w == "heading" and s.get("title") in ("Our Properties", "Explore", "About"):
            s["title"] = COLUMNS[state["col"]][0]
            state["pending_col"] = state["col"]
            state["col"] += 1

        elif w == "icon-list" and state.get("pending_col") is not None:
            items = []
            for text, url in COLUMNS[state["pending_col"]][1]:
                items.append({
                    "_id": new_id(text + url),
                    "text": text,
                    "link": link(url, "on" if url.startswith("http") and SITE not in url else ""),
                    "selected_icon": {"value": "", "library": ""},
                })
            s["icon_list"] = items
            state["pending_col"] = None

        elif w == "text-editor":
            editor = s.get("editor", "")
            if "growing collection of hospitality destinations" in editor:
                s["editor"] = INTRO
            elif "Privacy Policy" in editor:
                s["editor"] = BOTTOM_LINKS
            # The copyright line is already correct.

        elif w == "xpro-social-icon":
            for item in s.get("item", []):
                item["_id"] = new_id(item.get("_id", ""))

        transform(el.get("elements", []), state)


def main():
    with open(SRC, encoding="utf-8") as f:
        data = json.load(f)

    transform(data, {"col": 0, "pending_col": None})

    with open(OUT, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, separators=(",", ":"))

    # The sandbox loads the footer from disk rather than over the network, so
    # emit it as PHP too. Generated file: edit this script, not that one.
    payload = json.dumps(data, ensure_ascii=False, separators=(",", ":"))
    with open(OUT_PHP, "w", encoding="utf-8") as f:
        f.write(
            "<?php\n"
            "/**\n"
            " * Gateway Nova Ridge : footer template data.\n"
            " *\n"
            " * GENERATED by tools/build-novaridge-footer.py from the group site's own\n"
            " * footer (wordpress/templates/footer-11063.json). Every style setting is the\n"
            " * group footer's; only the link columns and copy differ. Do not hand-edit:\n"
            " * change the script and re-run it.\n"
            " */\n\n"
            "return json_decode( <<<'GWLJSON'\n" + payload + "\nGWLJSON\n, true );\n"
        )

    print(f"wrote {os.path.relpath(OUT, ROOT)} ({os.path.getsize(OUT)} bytes)")
    print(f"wrote {os.path.relpath(OUT_PHP, ROOT)} ({os.path.getsize(OUT_PHP)} bytes)")


if __name__ == "__main__":
    main()
