#!/usr/bin/env python3
"""Put the group site on the three properties' own photographs.

The group site was built before the shoots, so every picture on it was an AI
rendering: `image/image-<uuid>.jpg` and two `text_to_video` films. They showed
a pool, a restaurant and buildings that do not exist. This replaces every one
of them with a frame from the Nova Ridge, Lakeside or Tamale shoot.

Run it after changing a mapping below:

    python3 tools/group-real-photos.py

It is idempotent: a second run reports nothing left to do. It also reports any
`image/image-<uuid>` reference it does not know about, so a page added later
cannot quietly reintroduce one.

Descriptions come from PROPERTIES, where each photograph was described by what
is actually in its frame. Six filenames across the three shoots do not match
their contents, which is why nothing here is derived from a filename.
"""

import os
import re
import shutil
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
IMAGE = os.path.join(ROOT, "image")

# The two hero films, and the still each page shows until its film has loaded.
# A poster has to be a frame of the property the film is of, or the hero
# changes property the moment it starts playing.
HEROES = {
    "-t-e-x-t_-t-o_-v-i-d-e-o-51111e73-26d5-45e8-a7fd-2b51989fd771.mp4": (
        "group-hero.mp4", "lakeside/media/lakeside-tour.mp4",
        "lk-exterior-frontage.jpg"),
    "-t-e-x-t_-t-o_-v-i-d-e-o-ff7b67c5-f211-4bf5-b8a1-bd4865a7e949.mp4": (
        "group-hero-compact.mp4", "novaridge/media/novaridge-tour.mp4",
        "nr-balcony-skyline.jpg"),
}

# Every photograph the group site needs, as the name it takes in image/ mapped
# to its source frame and the description PROPERTIES gives it.
PHOTOS = {
    "lk-exterior-frontage.jpg": ("lakeside/media/exterior-frontage.jpg",
        "The frontage of Gateway Lodge Lakeside"),
    "lk-walkway.jpg": ("lakeside/media/walkway.jpg",
        "Walkway at Gateway Lodge Lakeside"),
    "lk-bedroom-suite.jpg": ("lakeside/media/bedroom-suite.jpg",
        "Suite bedroom at Gateway Lodge Lakeside"),
    "lk-hallway.jpg": ("lakeside/media/hallway.jpg",
        "Hallway through to the dining area at Gateway Lodge Lakeside"),
    "lk-living-dining.jpg": ("lakeside/media/living-dining.jpg",
        "Open living and dining area at Gateway Lodge Lakeside"),
    "lk-entrance-gate.jpg": ("lakeside/media/entrance-gate.jpg",
        "Gated entrance and paved forecourt at Gateway Lodge Lakeside"),
    "lk-bedroom-double.jpg": ("lakeside/media/bedroom-double.jpg",
        "Double bedroom at Gateway Lodge Lakeside"),
    "nr-balcony-skyline.jpg": ("novaridge/media/balcony-skyline.jpg",
        "Balcony seating overlooking Accra at Gateway Nova Ridge"),
    "nr-lounge-open-plan.jpg": ("novaridge/media/lounge-open-plan.jpg",
        "Open-plan living room and kitchen at Gateway Nova Ridge"),
    "nr-lounge-dining.jpg": ("novaridge/media/lounge-dining.jpg",
        "Living room with the dining table in the foreground at Gateway Nova Ridge"),
    "nr-lounge-daylight.jpg": ("novaridge/media/lounge-daylight.jpg",
        "Daylight through full-height curtains in the Nova Ridge living room"),
    "nr-dining-nook.jpg": ("novaridge/media/dining-nook.jpg",
        "Round dining table between two windows at Gateway Nova Ridge"),
    "nr-kitchen.jpg": ("novaridge/media/kitchen.jpg",
        "Fitted kitchen with oven, hob and marble splashback at Gateway Nova Ridge"),
    "tm-exterior-frontage.jpg": ("tamale/media/exterior-frontage.jpg",
        "Frontage and boundary wall at Gateway Lodge Tamale"),
    "tm-hero-courtyard.jpg": ("tamale/media/hero-courtyard.jpg",
        "Courtyard and two-storey frontage at Gateway Lodge Tamale"),
    "tm-terrace.jpg": ("tamale/media/terrace.jpg",
        "Shaded terrace seating at Gateway Lodge Tamale"),
    "tm-living-lounge.jpg": ("tamale/media/living-lounge.jpg",
        "Living room with sofas and coffee table at Gateway Lodge Tamale"),
    "tm-living-open.jpg": ("tamale/media/living-open.jpg",
        "Open living room with chandelier at Gateway Lodge Tamale"),
    "tm-bedroom-tv.jpg": ("tamale/media/bedroom-tv.jpg",
        "Bedroom with wall-mounted television at Gateway Lodge Tamale"),
    "tm-exterior-street.jpg": ("tamale/media/exterior-street.jpg",
        "Street view of Gateway Lodge Tamale"),
    "lk-kitchen-laundry.jpg": ("lakeside/media/kitchen-laundry.jpg",
        "Kitchen and laundry at Gateway Lodge Lakeside"),
    "lk-corridor.jpg": ("lakeside/media/corridor.jpg",
        "Walkway between the apartments at Gateway Lodge Lakeside"),
    "lk-living-seating.jpg": ("lakeside/media/living-seating.jpg",
        "Seating around the coffee table at Gateway Lodge Lakeside"),
    "lk-bedroom-king.jpg": ("lakeside/media/bedroom-king.jpg",
        "King bedroom at Gateway Lodge Lakeside"),
    "lk-bedroom-window.jpg": ("lakeside/media/bedroom-window.jpg",
        "Bedroom with full-height curtains at Gateway Lodge Lakeside"),
    "lk-balcony.jpg": ("lakeside/media/balcony.jpg",
        "Balcony at Gateway Lodge Lakeside"),
    "lk-exterior-approach.jpg": ("lakeside/media/exterior-approach.jpg",
        "The frontage of Gateway Lodge Lakeside, Lakeside Estate, Accra"),
    "nr-balcony.jpg": ("novaridge/media/balcony.jpg",
        "Balcony and glass balustrade at Gateway Nova Ridge"),
    "nr-bedroom-window.jpg": ("novaridge/media/bedroom-window.jpg",
        "Second bedroom with floor-to-ceiling curtains at Gateway Nova Ridge"),
    "nr-bedroom-suite.jpg": ("novaridge/media/bedroom-suite.jpg",
        "Bedroom with wardrobe wall at Gateway Nova Ridge"),
    "nr-lounge-sofas.jpg": ("novaridge/media/lounge-sofas.jpg",
        "Second bedroom and seating at Gateway Nova Ridge"),
    "tm-dining-nook.jpg": ("tamale/media/dining-nook.jpg",
        "Dining table for two at Gateway Lodge Tamale"),
    "tm-bedroom-second.jpg": ("tamale/media/bedroom-second.jpg",
        "Second bedroom with blue drapes at Gateway Lodge Tamale"),
    "tm-bedroom-window.jpg": ("tamale/media/bedroom-window.jpg",
        "Bedroom with curtained windows on two walls at Gateway Lodge Tamale"),
    "tm-entrance-gate.jpg": ("tamale/media/entrance-gate.jpg",
        "Signage and entrance gate at Gateway Lodge Tamale"),
}

# Each rendering, and the photographs that take its place. Chosen by what the
# rendering was standing in for on the page, not by what it looked like: a
# rendering of a swimming pool was illustrating the facilities section, and the
# facilities are real even though the pool in that frame never was.
#
# The list is read per page: a rendering used four times on one page - the
# offers grid reused one frame for every card - takes the first photograph the
# first time, the second the second time, and so on, so a page does not end up
# showing the same room four times. Beyond the end of the list it repeats the
# last entry.
SWAPS = {
    "image-726ee4b1-f8fd-45a0-bd12-98235b1d3c98.jpg": ["lk-walkway.jpg", "lk-kitchen-laundry.jpg", "lk-corridor.jpg"],
    "image-e84338d2-292b-4e16-82db-65269f104229.jpg": ["lk-bedroom-suite.jpg", "lk-bedroom-king.jpg"],
    "image-42a9e459-60d7-4042-b5d9-ff651d7e80f6.jpg": ["lk-hallway.jpg", "lk-corridor.jpg"],
    "image-bdf4aecc-320c-4451-a1b2-bc2469e37ced.jpg": ["tm-exterior-frontage.jpg"],
    "image-2221ed11-4bb2-49d0-af7f-c1d55ab53947.jpg": ["lk-exterior-frontage.jpg", "tm-entrance-gate.jpg"],
    "image-32cc0a19-3345-47c6-81ba-76d10e8b6001.jpg": ["nr-balcony-skyline.jpg", "nr-balcony.jpg", "tm-exterior-street.jpg"],
    "image-65ad891d-2ad7-492a-b776-40f624b7ab03.jpg": ["nr-lounge-dining.jpg", "lk-living-seating.jpg", "tm-dining-nook.jpg", "nr-bedroom-suite.jpg"],
    "image-582a9c52-1fa7-4697-9027-36bc3ba1af77.jpg": ["lk-balcony.jpg", "tm-terrace.jpg"],
    "image-b505567a-4117-4afb-8b34-facfa1b98092.jpg": ["nr-lounge-daylight.jpg", "nr-bedroom-window.jpg", "lk-bedroom-window.jpg", "tm-bedroom-window.jpg"],
    "image-78cadada-42bb-4487-a47a-2c7d7126c159.jpg": ["nr-dining-nook.jpg"],
    "image-31dc6997-454d-4999-9122-a5bcf0ddc382.jpg": ["lk-living-dining.jpg"],
    "image-c3e4ab2d-97c2-4af3-b7b1-7067fd9a8066.jpg": ["lk-exterior-frontage.jpg"],
    "image-7dbeff76-c7fa-49d7-9750-e6a8c133e839.jpg": ["nr-lounge-open-plan.jpg", "nr-balcony.jpg"],
    "image-116441e4-5483-482b-8883-db7d22cc4c09.jpg": ["tm-living-lounge.jpg", "lk-living-seating.jpg", "nr-lounge-sofas.jpg"],
    "image-116441e4-5483-482b-8883-db7d22cc4c09 (1).jpg": ["tm-living-lounge.jpg"],
    "image-86afc8c1-f6b4-4c5f-a8da-3f9abc3e9f79.jpg": ["tm-hero-courtyard.jpg"],
    "image-b03165c7-4895-41c6-85de-8ee3d17b06f0.jpg": ["lk-entrance-gate.jpg", "lk-exterior-approach.jpg"],
    "image-860bbcfd-9e64-4281-b392-e2d06ac5b441.jpg": ["tm-living-open.jpg"],
    "image-cbc2974c-4277-4e43-832e-e9c20cc4c58d.jpg": ["lk-bedroom-double.jpg"],
    "image-45421d00-ba5c-4150-b284-e0a5f04bd0aa.jpg": ["tm-bedroom-tv.jpg"],
    "image-55d0c8a8-cfd2-4749-85af-72ebd0f6df6f.jpg": ["nr-kitchen.jpg"],
    "image-03cfe057-a325-43eb-bae8-b48379d47a14.jpg": ["lk-exterior-frontage.jpg"],
    "-t-e-x-t_-t-o_-i-m-a-g-e-c1010ce0-dd09-4ec1-bd51-8fb6fc9ff0c3.jpg": ["tm-exterior-street.jpg"],
    "image-5a26ef1e-f476-4a80-bf57-012b7a8bbcc8.jpg": ["tm-exterior-frontage.jpg"],
    "image-63cb0fc5-015e-4c42-880f-f975b4971e52.jpg": ["tm-bedroom-second.jpg"],
    "image-bcb6015b-5fff-48cb-b747-b02360c2524f.jpg": ["lk-balcony.jpg"],
    "image-4b895856-f7f6-4245-829c-97aa7ac67f32.jpg": ["tm-entrance-gate.jpg"],
}


def install():
    """Copy every photograph and film the mapping needs into image/."""
    copied = []
    for name, (src, _alt) in PHOTOS.items():
        s, d = os.path.join(ROOT, src), os.path.join(IMAGE, name)
        if not os.path.exists(s):
            sys.exit(f"missing source photograph: {src}")
        if not os.path.exists(d) or os.path.getsize(s) != os.path.getsize(d):
            shutil.copy(s, d)
            copied.append(name)
    for _ai, (name, src, _poster) in HEROES.items():
        s, d = os.path.join(ROOT, src), os.path.join(IMAGE, name)
        if not os.path.exists(s):
            sys.exit(f"missing source film: {src}")
        if not os.path.exists(d) or os.path.getsize(s) != os.path.getsize(d):
            shutil.copy(s, d)
            copied.append(name)
    return copied


def rewrite(html):
    """One page. Returns the new html and how many references it changed."""
    n = 0

    # The hero comes first: it decides the page's poster, and the social
    # preview follows the poster rather than being mapped on its own.
    poster = None
    for ai, (film, _src, still) in HEROES.items():
        if ai in html:
            poster = still
            html = html.replace(f"image/{ai}", f"image/{film}")
            n += 1
    if poster:
        def fix_poster(m):
            return f'{m.group(1)}image/{poster}"'
        html, k = re.subn(r'(poster=")image/[^"]+"', fix_poster, html)
        n += k
        html, k = re.subn(
            r'((?:og|twitter):image"\s+content=")image/[^"]+"',
            lambda m: f'{m.group(1)}image/{poster}"', html)
        n += k

    # Then every remaining rendering, alt text included. An <img> keeps its
    # position in the page; only the frame and its description change. Each
    # rendering has its own counter, so the second and third uses of one
    # rendering on a page take the second and third photographs in its list.
    for ai, reals in SWAPS.items():
        if f"image/{ai}" not in html:
            continue
        seen = [0]

        def fix_tag(m, ai=ai, reals=reals, seen=seen):
            real = reals[min(seen[0], len(reals) - 1)]
            seen[0] += 1
            tag = m.group(0).replace(f"image/{ai}", f"image/{real}")
            if 'alt="' in tag:
                tag = re.sub(r'alt="[^"]*"', f'alt="{PHOTOS[real][1]}"', tag)
            return tag

        pattern = r'<(?:img|source)[^>]*' + re.escape(f"image/{ai}") + r'[^>]*>'
        html, k = re.subn(pattern, fix_tag, html)
        n += k
        # Anything left is a CSS background or a bare href, which carries no
        # description of its own, so it takes the first photograph.
        html, k = re.subn(re.escape(f"image/{ai}"), f"image/{reals[0]}", html)
        n += k
    return html, n


def main():
    copied = install()
    touched, total = [], 0
    for f in sorted(os.listdir(ROOT)):
        if not f.endswith(".html"):
            continue
        path = os.path.join(ROOT, f)
        html = open(path, encoding="utf-8").read()
        new, n = rewrite(html)
        if n:
            open(path, "w", encoding="utf-8").write(new)
            touched.append(f"{f} ({n})")
            total += n

    print(f"copied {len(copied)} file(s) into image/")
    print(f"rewrote {total} reference(s) across {len(touched)} page(s)")

    # Nothing generated may be left anywhere on the site.
    left = set()
    for f in sorted(os.listdir(ROOT)):
        if not f.endswith(".html"):
            continue
        html = open(os.path.join(ROOT, f), encoding="utf-8").read()
        for m in re.finditer(r'image/(image-[0-9a-f-]{8}[^"\')]*|-t-e-x-t_[^"\')]*)', html):
            left.add(f"{f}: {m.group(1)}")
    if left:
        print(f"\n{len(left)} generated reference(s) still on the site:")
        for x in sorted(left):
            print("  " + x)
        return 1
    print("no generated imagery left on any page")

    orphans = sorted(
        n for n in os.listdir(IMAGE)
        if re.match(r'(image-[0-9a-f-]{8}|-t-e-x-t_)', n))
    if orphans:
        print(f"\n{len(orphans)} generated file(s) now unreferenced in image/:")
        for o in orphans:
            print("  " + o)
    return 0


if __name__ == "__main__":
    sys.exit(main())
