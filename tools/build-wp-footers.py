#!/usr/bin/env python3
"""Derive each property's footer from the live group-site footer.

wordpress/templates/footer-11063.json is the export of the real gatewaylodgegroup.com
footer, and it is the approved design. This keeps every style setting in it
untouched and swaps only the content: the three link columns, the brand
paragraph and the bottom bar.

Emits wordpress/novamira-sandbox/gwl-property-footers.php, keyed by slug, which
the Elementor builder saves as each site's XPRO footer template.

Run from the repo root:  python3 tools/build-wp-footers.py
"""

import copy
import hashlib
import importlib.util
import json
import os

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, "wordpress", "templates", "footer-11063.json")
LANDINGS = os.path.join(ROOT, "tools", "build-landings.py")
OUT = os.path.join(ROOT, "wordpress", "novamira-sandbox", "gwl-property-footers.php")

GROUP = "https://www.gatewaylodgegroup.com"


def load_properties():
    spec = importlib.util.spec_from_file_location("build_landings", LANDINGS)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod.PROPERTIES


def link(url, external=""):
    return {"url": url, "is_external": external, "nofollow": ""}


class Transformer:
    """Ids must be unique per document, so derive fresh ones per property."""

    def __init__(self, slug, site, phone, email, short):
        self.slug = slug
        self.seen = set()
        self.site = site
        self.short = short
        tel = "tel:" + phone.replace(" ", "")
        whatsapp = "https://wa.me/" + phone.replace(" ", "").replace("+", "")
        self.columns = [
            (short, [
                ("Home", site + "/"),
                ("About", site + "/about/"),
                ("Facilities", site + "/facilities/"),
                ("Contact", site + "/contact/"),
            ]),
            ("Gateway Lodge Group", [
                ("Group Website", GROUP + "/"),
                ("Our Properties", GROUP + "/properties/"),
                ("Offers", GROUP + "/offers/"),
                ("About Us", GROUP + "/about-us/"),
            ]),
            ("Reservations", [
                (phone, tel),
                ("WhatsApp", whatsapp),
                (email, "mailto:" + email),
            ]),
        ]
        self.col = 0
        self.pending = None

    def new_id(self, old):
        for salt in range(200):
            h = hashlib.md5(f"{self.slug}-footer:{old}:{salt}".encode()).hexdigest()[:7]
            if h not in self.seen:
                self.seen.add(h)
                return h
        raise RuntimeError("could not allocate id")

    def walk(self, elements, intro):
        for el in elements:
            if "id" in el:
                el["id"] = self.new_id(el["id"])
            s = el.setdefault("settings", {})
            w = el.get("widgetType")

            if el.get("elType") == "container" and any(
                k.get("widgetType") == "xpro-site-logo" for k in el.get("elements", [])
            ):
                # The brand lockup is a nested row, and Elementor's default 10px
                # container padding insets it from the column edge, so the logo sits
                # lower and further right than the headings in the columns beside it.
                s["padding"] = {"unit": "px", "top": "0", "right": "0",
                                "bottom": "0", "left": "0", "isLinked": "1"}
                s["flex_align_items"] = "center"

            if el.get("elType") == "container" and s.get("flex_justify_content") == "space-between":
                # The copyright bar keeps its two halves side by side on a phone, which
                # overflows and drags both flush against the left edge of the screen.
                s["flex_direction_mobile"] = "column"
                s["flex_align_items_mobile"] = "flex-start"
                # Its explicit padding also overrides the boxed container's default
                # 10px gutter on mobile, leaving the text flush with the screen edge.
                s["padding_mobile"] = {"unit": "px", "top": "20", "right": "20",
                                       "bottom": "0", "left": "20", "isLinked": ""}

            if w == "heading" and s.get("title") == "Gateway Lodge Group":
                # The brand lockup beside the logo links back to this site's home.
                s["link"] = link(self.site + "/")

            elif w == "heading" and s.get("title") in ("Our Properties", "Explore", "About"):
                s["title"] = self.columns[self.col][0]
                self.pending = self.col
                self.col += 1

            elif w == "icon-list" and self.pending is not None:
                items = []
                for text, url in self.columns[self.pending][1]:
                    external = "on" if url.startswith("http") and self.site not in url else ""
                    items.append({
                        "_id": self.new_id(text + url),
                        "text": text,
                        "link": link(url, external),
                        "selected_icon": {"value": "", "library": ""},
                    })
                s["icon_list"] = items
                self.pending = None

            elif w == "text-editor":
                editor = s.get("editor", "")
                if "growing collection of hospitality destinations" in editor:
                    s["editor"] = intro
                elif "Privacy Policy" in editor:
                    s["editor"] = (
                        '<p><a style="color:rgba(255,255,255,0.55)" href="{g}/privacy-policy-2/">'
                        'Privacy Policy</a> &nbsp;&nbsp; '
                        '<a style="color:rgba(255,255,255,0.55)" href="{g}/terms/">'
                        'Terms &amp; Conditions</a></p>'
                    ).format(g=GROUP)
                    s["align_mobile"] = "left"

            elif w == "xpro-site-logo":
                # The widget defaults to the 150px thumbnail at full size, which makes
                # the brand lockup three times taller than the link columns beside it.
                # Cap it so the logo row lines up with the other columns' headings.
                s["width"] = {"unit": "px", "size": 32}
                s["height"] = {"unit": "px", "size": 32}
                s["object-fit"] = "contain"

            elif w == "xpro-social-icon":
                for item in s.get("item", []):
                    item["_id"] = self.new_id(item.get("_id", ""))
                # The wrapper is a CSS grid whose column count defaults to 3, so six
                # icons break onto a second row. One column per icon keeps them inline.
                s["social_icon_column_grid"] = str(max(1, min(6, len(s.get("item", [])))))
                s.pop("social_icon_spacing", None)  # not a control on this widget
                s["social_icon_item_space_vertical"] = {"unit": "px", "size": 12}
                s["social_item_space_between"] = {"unit": "px", "size": 12}

            self.walk(el.get("elements", []), intro)


def main():
    with open(SRC, encoding="utf-8") as f:
        base = json.load(f)

    footers = {}
    for prop in load_properties():
        data = copy.deepcopy(base)
        site = f"https://{prop['domain']}"
        intro = (f"<p>{prop['name']} is part of Gateway Lodge Group, a growing collection of "
                 f"hospitality destinations across Ghana.</p>")
        Transformer(prop["slug"], site, prop["phone"], prop["email"], prop["short"]).walk(data, intro)
        footers[prop["slug"]] = data

    payload = json.dumps(footers, ensure_ascii=False, separators=(",", ":"))
    with open(OUT, "w", encoding="utf-8") as f:
        f.write(
            "<?php\n"
            "/**\n"
            " * Gateway Lodge Group : XPRO footer template data, keyed by property slug.\n"
            " *\n"
            " * GENERATED by tools/build-wp-footers.py from the group site's own footer\n"
            " * (wordpress/templates/footer-11063.json). Every style setting is the group\n"
            " * footer's; only the link columns and copy differ per property. Do not\n"
            " * hand-edit: change the script and re-run it.\n"
            " */\n\n"
            "return json_decode( <<<'GWLJSON'\n" + payload + "\nGWLJSON\n, true );\n"
        )
    print(f"wrote {os.path.relpath(OUT, ROOT)} ({os.path.getsize(OUT)} bytes)"
          f" for {', '.join(footers)}")


if __name__ == "__main__":
    main()
