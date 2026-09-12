#!/usr/bin/env python3
"""Emit the WordPress builder's per-property content from the static-page source.

tools/build-landings.py holds the copy for all three properties. Rather than
retype it into PHP and let the two drift, this reads PROPERTIES from there and
writes wordpress/novamira-sandbox/gwl-property-content.php, which the Elementor
builder loads.

Only the handful of fields the static pages have no equivalent for are declared
here: the inner-page banners, where each property's gallery splits between Home
and Facilities, the FAQ pairs, and the per-page SEO.

Run from the repo root:  python3 tools/build-wp-property-content.py
"""

import importlib.util
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, "tools", "build-landings.py")
OUT = os.path.join(ROOT, "wordpress", "novamira-sandbox", "gwl-property-content.php")

# The static pages draw inline SVGs from a local table; Elementor wants Font
# Awesome classes. Same concepts, different vocabulary.
ICONS = {
    "wifi": "fas fa-wifi",
    "kitchen": "fas fa-utensils",
    "ac": "fas fa-snowflake",
    "parking": "fas fa-car",
    "power": "fas fa-bolt",
    "laundry": "fas fa-tshirt",
    "tv": "fas fa-tv",
    "concierge": "fas fa-concierge-bell",
    "security": "fas fa-shield-alt",
    "workspace": "fas fa-laptop",
    "balcony": "fas fa-tree",
    "water": "fas fa-tint",
    "lift": "fas fa-elevator",
    "family": "fas fa-users",
    "airport": "fas fa-plane",
    "housekeeping": "fas fa-broom",
}

# Written here because the static pages carry no FAQ section.
FAQS = {
    "novaridge": [
        ("Is the whole apartment mine?",
         "Yes. Nova Ridge is a single unit, so it is never shared or split between parties."),
        ("Do you take long stays?",
         "We do. The kitchen, laundry and workspace are built for stays measured in weeks rather "
         "than nights. Ask reservations about the long-stay rate."),
        ("Is parking included?",
         "Yes, one gated parking space within the building comes with the apartment."),
        ("What happens in a power cut?",
         "The building runs on standby power, so the apartment stays lit and cooled."),
    ],
    "lakeside": [
        ("Can you take a group?",
         "Yes. Twelve rooms and units means a family, a team or a small event can stay under one "
         "roof. Tell reservations how many of you there are and we will hold the right mix."),
        ("Is breakfast included?",
         "Breakfast is served daily, with lunch and dinner cooked to order. Ask when you book."),
        ("How far is the airport?",
         "About 40 minutes by car to Kotoka International Airport, traffic depending."),
        ("Is there parking?",
         "Yes, on-site parking for guests at no extra charge, behind a manned gate."),
    ],
    "tamale": [
        ("Do you take long stays?",
         "Yes, and most guests are here for a stretch. Every apartment has its own kitchen and "
         "laundry, and there is a rate for stays by the month."),
        ("One bedroom or two?",
         "Both. One-bedroom apartments suit a single traveller or a pair; two-bedroom apartments "
         "suit families and colleagues sharing."),
        ("Is the compound secure?",
         "It is a walled, purpose-built block with courtyard parking, a manned gate and security "
         "on site day and night."),
        ("What happens in a power cut?",
         "Standby generation covers the whole block, and water is stored and pumped rather than "
         "depending on the mains."),
    ],
}

# The About page runs three splits whose copy has no static-page equivalent.
ABOUT_SECTIONS = {
    "novaridge": [
        ("Who we are", "A hotel standard, in a home that is only yours.", [
            "Gateway Nova Ridge is the smallest and most private of the three Gateway Lodge "
            "properties: one apartment, on an upper floor of a residential tower in Ridge. There "
            "is no reception floor and no corridor of other guests. You let yourself in, and for "
            "as long as you stay the apartment belongs to you.",
            "Inside there is an open living room that runs into a full kitchen, a dining table, "
            "two bedrooms dressed in hotel linen, two bathrooms, and a balcony looking over the "
            "green of Ridge towards the city.",
        ]),
        ("The address", "Ridge, where Accra keeps its quiet.", [
            "Ridge is the district of embassies, ministries and old trees, a few minutes from the "
            "central business district but a world away from its noise. It is the address people "
            "choose when they want to be close to everything and hear none of it.",
        ]),
        ("How we run it", "Serviced, not staffed over.", [
            "Housekeeping comes on a rhythm that suits your stay rather than a fixed hotel "
            "schedule. Laundry, pressing, airport transfers and grocery runs are arranged on "
            "request. One number reaches the team at any hour.",
        ]),
    ],
    "lakeside": [
        ("Who we are", "The largest of the three, and the calmest.", [
            "Gateway Lodge Lakeside sits east of the centre in the Lakeside Estate, far enough "
            "out that the evenings are quiet and close enough that the city is still a drive "
            "rather than a journey. Twelve rooms and units, a front desk staffed around the "
            "clock, and grounds you can actually walk in.",
            "It is the property guests choose when they are travelling as a family, when a team "
            "needs several rooms under one roof, or when the point of the trip is to slow down "
            "for a few days.",
        ]),
        ("The address", "Far enough out that the evening goes quiet.", [
            "Lakeside Estate is residential rather than commercial, so the noise stops when the "
            "day does. The eastern suburbs and the Accra to Aburi road are close, and the centre "
            "is a straightforward drive rather than a fight through traffic.",
        ]),
        ("How we run it", "A desk that is always answered.", [
            "Twelve units is the size where service either holds or slips. Lakeside is staffed to "
            "hold it: a front desk through the night for late arrivals, breakfast every morning "
            "with lunch and dinner to order, and housekeeping that does not need chasing.",
        ]),
    ],
    "tamale": [
        ("Who we are", "A base in the north, not a room for the night.", [
            "Tamale is the working capital of northern Ghana, and most people who come here come "
            "for a stretch: a project, a posting, a season of fieldwork. Gateway Lodge Tamale is "
            "built for exactly that: six fully furnished apartments in a purpose-built, walled "
            "block, in one and two-bedroom layouts.",
            "Each apartment has its own kitchen, its own living room and its own bathroom, so a "
            "long stay does not mean living out of a suitcase.",
        ]),
        ("The address", "Tamale, and the work that brings you here.", [
            "The block sits within reach of the central business area and the markets, a short "
            "drive from Tamale International Airport, and convenient for Northern Region project "
            "sites and NGO offices. Banks, fuel and supermarkets are close by.",
        ]),
        ("How we run it", "Serviced around a working week.", [
            "Housekeeping and fresh linen come at a rhythm that suits the length of your stay "
            "rather than a fixed hotel schedule. Laundry, pressing and airport transfers are "
            "arranged on request, and the gate is manned day and night.",
        ]),
    ],
}

BANNER_SUBS = {
    "novaridge": {
        "about": "A single serviced apartment in Ridge, run to the Gateway Lodge standard.",
        "facilities": "Everything an extended stay asks for is already fitted.",
        "contact": "Reservations answer by phone and WhatsApp every day.",
    },
    "lakeside": {
        "about": "Twelve rooms and units on the quiet side of Accra.",
        "facilities": "A staffed desk, dining on site and grounds to walk in.",
        "contact": "Reservations answer by phone and WhatsApp every day.",
    },
    "tamale": {
        "about": "Six furnished apartments in a walled block in Tamale.",
        "facilities": "Private kitchens, laundry and power that holds.",
        "contact": "Reservations answer by phone and WhatsApp every day.",
    },
}


# The Contact page banner, and how the gallery splits between Home and Facilities.
LAYOUT = {
    "novaridge": {"banner_contact": "hallway", "home_gallery": 6},
    # Named rather than left to fall through to gallery[-1], which moves every
    # time an image is added to the set.
    "lakeside": {"banner_contact": "entrance-gate", "home_gallery": 6},
    "tamale": {"banner_contact": "stairwell", "home_gallery": 6},
}

SEO = {
    "novaridge": {
        "about": ("About Gateway Nova Ridge | Ridge, Accra",
                  "One serviced apartment on an upper floor in Ridge, Accra. No lobby, no shared "
                  "corridor: the apartment is yours alone for the length of your stay."),
        "facilities": ("Facilities & Amenities | Gateway Nova Ridge, Accra",
                       "Full kitchen, in-apartment laundry, air conditioning, backup power, secure "
                       "parking, private balcony and 24-hour support at Gateway Nova Ridge."),
        "contact": ("Contact & Location | Gateway Nova Ridge, Ridge, Accra",
                    "Find Gateway Nova Ridge in Ridge, Accra. Call or WhatsApp reservations, or "
                    "send the enquiry form for availability and rates."),
    },
    "lakeside": {
        "about": ("About Gateway Lodge Lakeside | Accra",
                  "Twelve serviced rooms and units in Lakeside Estate, Accra: quiet surroundings, "
                  "a staffed front desk and dining on site."),
        "facilities": ("Facilities & Amenities | Gateway Lodge Lakeside, Accra",
                       "24-hour front desk, on-site dining, daily housekeeping, backup power, "
                       "grounds and ample parking at Gateway Lodge Lakeside."),
        "contact": ("Contact & Location | Gateway Lodge Lakeside, Accra",
                    "Find Gateway Lodge Lakeside in Lakeside Estate, Accra. Call or WhatsApp "
                    "reservations, or send the enquiry form."),
    },
    "tamale": {
        "about": ("About Gateway Lodge Tamale | Northern Region",
                  "Six fully furnished one and two-bedroom serviced apartments in Tamale, built "
                  "for stays measured in weeks rather than nights."),
        "facilities": ("Facilities & Amenities | Gateway Lodge Tamale",
                       "Private kitchens, in-apartment laundry, air conditioning, backup power, "
                       "gated parking and 24-hour security at Gateway Lodge Tamale."),
        "contact": ("Contact & Location | Gateway Lodge Tamale, Northern Region",
                    "Find Gateway Lodge Tamale in the Northern Region. Call or WhatsApp "
                    "reservations, or send the enquiry form."),
    },
}


def load_module():
    spec = importlib.util.spec_from_file_location("build_landings", SRC)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


def media_refs(prop):
    return load_module().media_refs(prop)


def load_properties():
    spec = importlib.util.spec_from_file_location("build_landings", SRC)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod.PROPERTIES


def key(path):
    """media/hero-living.jpg -> hero-living, the name used in gwl_media_map."""
    return os.path.splitext(os.path.basename(path))[0] if path else ""


def php(value, indent=1):
    pad = "\t" * indent
    if isinstance(value, dict):
        rows = [f"{pad}\t{php(k, 0)} => {php(v, indent + 1)}," for k, v in value.items()]
        return "array(\n" + "\n".join(rows) + f"\n{pad})"
    if isinstance(value, (list, tuple)):
        rows = [f"{pad}\t{php(v, indent + 1)}," for v in value]
        return "array(\n" + "\n".join(rows) + f"\n{pad})"
    if value is None:
        return "null"
    if isinstance(value, bool):
        return "true" if value else "false"
    if isinstance(value, (int, float)):
        return str(value)
    return "'" + str(value).replace("\\", "\\\\").replace("'", "\\'") + "'"


def strip_tags(text):
    return re.sub(r"</?p>", "", text)


def media_manifest(prop):
    """key -> path in the repo, for every image and film the pages reference.

    gwl_media() looks each element up in the `gwl_media_map` option by key, and
    a key that is missing fails silently: the widget is dropped and the page
    renders without it. Emitting the manifest here means the upload step can be
    driven from the same source as the copy, and can say what it could not find.
    """
    paths = {}
    for _, rel in media_refs(prop):
        paths[key(rel)] = f"{prop['slug']}/{rel}"
    return dict(sorted(paths.items()))


def build(prop):
    slug = prop["slug"]
    layout = LAYOUT[slug]
    gallery = [key(g[0]) for g in prop["gallery"]]
    split = layout["home_gallery"]

    return {
        "slug": slug,
        "property": prop["name"],
        "short": prop["short"],
        "locality": prop["locality"],
        "region": prop["region"],
        "address": prop["address"],
        "units": prop["units"],
        "phone": prop["phone"],
        "whatsapp": "https://wa.me/" + prop["phone"].replace(" ", "").replace("+", ""),
        "email": prop["email"],
        "group": "https://www.gatewaylodgegroup.com",
        "site": f"https://{prop['domain']}",
        "map_address": prop["address"],
        "tagline": prop["tagline"],
        "meta_desc": prop["meta_desc"],

        "hero_video": key(prop["hero_video"]) if prop["hero_video"] else None,
        "hero_poster": key(prop["hero_poster"]),

        "stats": [list(s) for s in prop["stats"]],
        "pillars": [list(p) for p in prop["pillars"]],

        "about_kicker": prop["about_kicker"],
        "about_head": prop["about_head"],
        "about_body": [strip_tags(p) for p in prop["about_body"]],
        "about_image": key(prop["about_image"]),

        "rooms": [[key(r[0]), r[2], r[3]] for r in prop["rooms"]],

        "facilities_head": prop["facilities_head"],
        "facilities_body": prop["facilities_body"],
        "facilities_image": key(prop["facilities_image"]),
        "facilities": [[ICONS[f[0]], f[1], f[2]] for f in prop["facilities"]],

        "gallery_home": gallery[:split],
        "gallery_facilities": gallery[split:],

        "banner_about": key(prop["about_image"]),
        "banner_facilities": key(prop["facilities_image"]),
        "banner_contact": layout["banner_contact"] or gallery[-1],

        "about_sections": [
            {"kicker": k, "head": h, "paras": p}
            for k, h, p in ABOUT_SECTIONS[slug]
        ],
        "banner_subs": BANNER_SUBS[slug],

        # Every file the build will ask for, as key => repo path, so the media
        # upload is driven by the same source of truth as the pages rather than
        # by PHP typed out by hand each time a property goes up.
        "media": media_manifest(prop),

        "location_points": prop["location_points"],
        "faqs": [list(f) for f in FAQS[slug]],
        "seo": {k: list(v) for k, v in SEO[slug].items()},
    }


def main():
    props = {p["slug"]: build(p) for p in load_properties()}

    body = "<?php\n"
    body += "/**\n"
    body += " * Gateway Lodge Group : per-property content for the Elementor builder.\n"
    body += " *\n"
    body += " * GENERATED by tools/build-wp-property-content.py from PROPERTIES in\n"
    body += " * tools/build-landings.py, so the WordPress build and the static pages cannot\n"
    body += " * drift apart. Do not hand-edit: change the copy at the source and re-run.\n"
    body += " */\n\n"
    body += "return " + php(props, 0) + ";\n"

    with open(OUT, "w", encoding="utf-8") as f:
        f.write(body)
    print(f"wrote {os.path.relpath(OUT, ROOT)} ({os.path.getsize(OUT)} bytes)"
          f" for {', '.join(props)}")


if __name__ == "__main__":
    main()
