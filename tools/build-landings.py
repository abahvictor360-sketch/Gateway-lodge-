#!/usr/bin/env python3
"""Generate the property subdomain landing pages.

Each property gets a self-contained folder (index.html + landing.css + media/)
so it can be deployed to its own subdomain without the rest of the site:

    novaridge/  ->  novaridge.gatewaylodgegroup.com
    lakeside/   ->  lakeside.gatewaylodgegroup.com
    tamale/     ->  tamale.gatewaylodgegroup.com

Run from the repo root:  python3 tools/build-landings.py
"""

import os
import shutil

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
GROUP = "https://www.gatewaylodgegroup.com"

LOGO = (
    '<svg width="34" height="34" viewBox="0 0 100 100" fill="none" '
    'xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
    '<path d="M50 6 L94 50 L50 94 L6 50 Z" fill="#dba845"/>'
    '<path d="M50 6 L74 30 L38 30 Z" fill="#ffffff"/>'
    '<path d="M50 22 C34 22 24 32 24 48 C24 62 34 72 48 72 L48 62 C38 62 34 56 34 48 '
    'C34 38 40 32 50 32 C60 32 66 38 66 46 L44 46 L44 56 L76 56 L76 46 C76 30 66 22 50 22 Z" '
    'fill="#5c1620"/></svg>'
)

FAVICON = (
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E"
    "%3Cpath d='M50 6 L94 50 L50 94 L6 50 Z' fill='%23dba845'/%3E"
    "%3Cpath d='M50 6 L74 30 L38 30 Z' fill='%23ffffff'/%3E"
    "%3Cpath d='M50 22 C34 22 24 32 24 48 C24 62 34 72 48 72 L48 62 C38 62 34 56 34 48 "
    "C34 38 40 32 50 32 C60 32 66 38 66 46 L44 46 L44 56 L76 56 L76 46 C76 30 66 22 50 22 Z' "
    "fill='%235c1620'/%3E%3C/svg%3E"
)

SOCIALS = [
    ("Facebook", "https://facebook.com/gatewaylodgegroup",
     '<path d="M14 8.5h-2c-.3 0-.6.3-.6.6V11h2.4l-.3 2.5h-2.1V19h-2.6v-5.5H7v-2.5h1.8V9.4c0-1.8 1.1-3.4 3.6-3.4h1.6v2.5Z" fill="currentColor"/>'),
    ("Instagram", "https://instagram.com/gatewaylodgegroup",
     '<rect x="6" y="6" width="12" height="12" rx="3.5" stroke="currentColor" stroke-width="1.6" fill="none"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6" fill="none"/><circle cx="15.6" cy="8.4" r="0.9" fill="currentColor"/>'),
    ("LinkedIn", "https://linkedin.com/company/gatewaylodgegroup",
     '<rect x="6" y="10" width="2.2" height="8" fill="currentColor"/><circle cx="7.1" cy="7.2" r="1.3" fill="currentColor"/><path d="M11 10h2.1v1.2c.5-.8 1.4-1.4 2.6-1.4 2 0 3.3 1.3 3.3 3.9V18h-2.2v-3.9c0-1.1-.5-1.8-1.5-1.8-1 0-1.6.7-1.6 1.8V18H11v-8Z" fill="currentColor"/>'),
    ("WhatsApp", "https://wa.me/233240000000",
     '<path d="M12 6a6 6 0 0 0-5.1 9.2L6 18l2.9-.9A6 6 0 1 0 12 6Zm3.2 8.5c-.1.4-.8.8-1.2.9-.3 0-.7.1-2.2-.5-1.9-.8-3.1-2.7-3.2-2.8-.1-.1-.8-1-.8-1.9 0-.9.5-1.3.6-1.5.2-.2.4-.2.5-.2h.4c.1 0 .3 0 .5.4l.6 1.5c.1.1.1.3 0 .4l-.4.5c-.1.1-.2.3 0 .5.2.3.7 1.1 1.5 1.7.9.7 1.6.9 1.9 1 .2.1.4.1.5-.1l.5-.6c.2-.2.4-.2.6-.1l1.3.6c.2.1.3.2.3.3 0 .1 0 .5-.1.9Z" fill="currentColor"/>'),
]

ICONS = {
    "wifi": '<path d="M4 9a12 12 0 0 1 16 0M7 13a7 7 0 0 1 10 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none"/><circle cx="12" cy="17.5" r="1.4" fill="currentColor"/>',
    "kitchen": '<rect x="5" y="4" width="14" height="16" rx="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M5 11h14M9 7v1.5M9 14.5V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
    "ac": '<rect x="4" y="6" width="16" height="6" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M8 15v2M12 15v3M16 15v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
    "parking": '<rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M10 16V8h2.6a2.4 2.4 0 0 1 0 4.8H10" stroke="currentColor" stroke-width="1.6" fill="none"/>',
    "power": '<path d="M13 3 6 13h5l-1 8 7-10h-5l1-8Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" fill="none"/>',
    "laundry": '<rect x="5" y="3" width="14" height="18" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="12" cy="14" r="4" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="8.5" cy="6.5" r="0.9" fill="currentColor"/>',
    "tv": '<rect x="3" y="5" width="18" height="11" rx="1.6" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M9 20h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
    "concierge": '<path d="M4 18h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M5.5 18a6.5 6.5 0 0 1 13 0" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="12" cy="5.5" r="1.4" fill="currentColor"/>',
    "security": '<path d="M12 3.5 19 6v6c0 4-3 7-7 8.5C8 19 5 16 5 12V6l7-2.5Z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none"/>',
    "workspace": '<rect x="3" y="5" width="18" height="10" rx="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M3 19h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
    "balcony": '<path d="M4 12h16v8H4z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M8 12v8M12 12v8M16 12v8M6 12V8a6 6 0 0 1 12 0v4" stroke="currentColor" stroke-width="1.5" fill="none"/>',
    "water": '<path d="M12 3.5s6 6.4 6 10.1a6 6 0 1 1-12 0C6 9.9 12 3.5 12 3.5Z" stroke="currentColor" stroke-width="1.5" fill="none"/>',
    "lift": '<rect x="5" y="3" width="14" height="18" rx="1.6" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M9.5 10 12 7l2.5 3M9.5 14 12 17l2.5-3" stroke="currentColor" stroke-width="1.4" fill="none"/>',
    "family": '<circle cx="8.5" cy="8" r="2.2" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="16" cy="9.5" r="1.8" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M4 19c0-2.5 2-4.4 4.5-4.4S13 16.5 13 19M13.5 19c0-2 1.2-3.4 3-3.4s3 1.4 3 3.4" stroke="currentColor" stroke-width="1.5" fill="none"/>',
    "airport": '<path d="M3 13.5 21 8l-2 4-6 1.5-3.5 5-1.8-.6.8-4.2-3.6.9L3 13.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" fill="none"/>',
    "housekeeping": '<path d="M6 21V9l4-6 4 6v12" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M6 13h8" stroke="currentColor" stroke-width="1.5"/>',
}

CHECK = ('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
         '<path d="M5 12l4 4 10-10" stroke="currentColor" stroke-width="2" '
         'stroke-linecap="round" stroke-linejoin="round"/></svg>')

PROPERTIES = [
    {
        "slug": "novaridge",
        "name": "Gateway Nova Ridge",
        "short": "Nova Ridge",
        "domain": "novaridge.gatewaylodgegroup.com",
        "locality": "Ridge, Accra",
        "region": "Greater Accra",
        "address": "4 Drake Avenue area, Ridge, Accra, Ghana",
        "units": "1 Unit",
        "tagline": "A private high-rise residence in Accra&rsquo;s most established address, "
                   "kept to the Gateway Lodge standard and yours alone for the length of your stay.",
        "meta_desc": "Gateway Nova Ridge is a private, fully serviced apartment in Ridge, Accra: "
                     "one unit, floor-to-ceiling city views and the full Gateway Lodge "
                     "Group standard of hospitality. Book direct.",
        "hero_video": "media/novaridge-tour.mp4",
        "hero_poster": "media/hero-living.jpg",
        "map": "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.730840917511!2d-0.18571442574026037!3d5.6067172331391015!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfdf9b017a90efd1%3A0x16994230d97951f4!2s4%20Drake%20Ave%2C%20Accra%2C%20Ghana!5e0!3m2!1sen!2sng!4v1788627007420!5m2!1sen!2sng",
        "stats": [("1", "Private unit"), ("2", "Bedrooms"), ("24/7", "Guest support"), ("15 min", "To the airport")],
        "about_image": "media/lounge-open-plan.jpg",
        "about_image_alt": "Open-plan living room and kitchen at Gateway Nova Ridge",
        "about_kicker": "The Residence",
        "about_head": "One apartment. No lobby, no queue, no neighbours in the corridor.",
        "about_body": [
            "Nova Ridge is a single serviced apartment on an upper floor of a residential tower in "
            "Ridge, the quiet tree-lined district that sits between central Accra&rsquo;s "
            "business addresses and the embassies. You are not checking into a hotel floor. You "
            "arrive to an apartment that has been prepared for you and stays that way.",
            "Inside: an open living room that runs into a full kitchen, a dining table, two "
            "bedrooms dressed in hotel linen, two bathrooms, and a balcony that looks out over the "
            "green of Ridge towards the city. It suits the guest who is in Accra for a fortnight "
            "as easily as the one here for two nights.",
        ],
        "pillars": [
            ("Yours alone", "A single unit, so the apartment is never shared, split or reassigned mid-stay."),
            ("Built for longer stays", "A real kitchen, laundry and a desk: the things that matter after night three."),
            ("A settled address", "Ridge puts the ministries, the CBD and Kotoka within an easy drive."),
        ],
        "rooms": [
            ("media/bedroom-king.jpg", "King bedroom with upholstered headboard at Gateway Nova Ridge",
             "Principal Bedroom", "A king bed, blackout curtains, fitted wardrobes and an en-suite bathroom."),
            ("media/bedroom-second.jpg", "Second bedroom at Gateway Nova Ridge",
             "Second Bedroom", "A second double room with its own wardrobe and mirror, for family or a colleague."),
            ("media/lounge-tv-wall.jpg", "Living room with smart television at Gateway Nova Ridge",
             "Living Room", "Deep seating for six, a smart television and light on two sides through the day."),
            ("media/kitchen-wide.jpg", "Full fitted kitchen at Gateway Nova Ridge",
             "Kitchen &amp; Dining", "A full fitted kitchen with oven, hob, microwave and a dining table for four."),
        ],
        "facilities_image": "media/kitchen.jpg",
        "facilities_image_alt": "Fitted kitchen with oven, hob and marble splashback at Gateway Nova Ridge",
        "facilities_head": "Everything the apartment needs, already in it",
        "facilities_body": "Nothing here is an upgrade or an extra line on the folio. The apartment "
                           "arrives complete, and the team keeps it that way for as long as you stay.",
        "facilities": [
            ("wifi", "High-Speed Wi-Fi", "Fibre throughout the apartment, steady enough for calls and uploads."),
            ("kitchen", "Full Kitchen", "Oven, hob, microwave, fridge-freezer, kettle and a stocked utensil drawer."),
            ("ac", "Air Conditioning", "Individually controlled in every bedroom and the living room."),
            ("power", "Backup Power", "The building runs on standby power, so the apartment stays on."),
            ("laundry", "Laundry", "In-apartment washing, with a pressing and laundry service on request."),
            ("workspace", "Desk &amp; Workspace", "A proper surface to work from, not a chair pulled up to the dining table."),
            ("balcony", "Private Balcony", "Table and chairs, and a long view over the Ridge treeline."),
            ("parking", "Secure Parking", "Gated, monitored parking within the building for one vehicle."),
            ("security", "24-Hour Security", "Manned entry and CCTV across the building&rsquo;s common areas."),
            ("concierge", "Guest Support", "One number, answered at any hour, for anything the stay needs."),
            ("lift", "Lift Access", "Serviced lifts to the apartment floor."),
            ("housekeeping", "Housekeeping", "Scheduled servicing and fresh linen, arranged around you."),
        ],
        "gallery": [
            ("media/hero-living.jpg", "Living room and entrance hall at Gateway Nova Ridge"),
            ("media/lounge-tv-wall.jpg", "Lounge seating facing the television wall at Gateway Nova Ridge"),
            ("media/kitchen-dining.jpg", "Kitchen and dining area at Gateway Nova Ridge"),
            ("media/bedroom-suite.jpg", "Bedroom with wardrobe wall at Gateway Nova Ridge"),
            ("media/balcony-skyline.jpg", "Balcony seating overlooking Accra at Gateway Nova Ridge"),
            ("media/lounge-daylight.jpg", "Daylight through full-height curtains in the Nova Ridge living room"),
            ("media/bedroom-window.jpg", "Second bedroom with floor-to-ceiling curtains at Gateway Nova Ridge"),
            ("media/dining-nook.jpg", "Round dining table between two windows at Gateway Nova Ridge"),
            ("media/bathroom.jpg", "En-suite bathroom at Gateway Nova Ridge"),
            ("media/hallway.jpg", "Hallway leading to the bedrooms at Gateway Nova Ridge"),
            ("media/lounge-sofas.jpg", "Second bedroom and seating at Gateway Nova Ridge"),
            ("media/balcony.jpg", "Balcony and glass balustrade at Gateway Nova Ridge"),
        ],
        "location_points": [
            "Walking distance to Ridge&rsquo;s embassies and government offices",
            "About 15 minutes by car to Kotoka International Airport",
            "Ten minutes to Osu, Airport Residential and the CBD",
            "Ridge Hospital and Accra&rsquo;s main clinics close by",
        ],
        "phone": "+233 24 000 0000",
        "email": "reservations@gatewaylodgegroup.com",
    },
    {
        "slug": "lakeside",
        "name": "Gateway Lodge Lakeside",
        "short": "Lakeside",
        "domain": "lakeside.gatewaylodgegroup.com",
        "locality": "Lakeside, Accra",
        "region": "Greater Accra",
        "address": "Lakeside Estate, Accra, Ghana",
        "units": "12 Units",
        "tagline": "Twelve serviced apartments in Lakeside Estate, on the quiet side of Accra, "
                   "with a full kitchen in every one.",
        "meta_desc": "Gateway Lodge Lakeside offers twelve serviced apartments in Lakeside "
                     "Estate, Accra, each with a fitted kitchen, laundry and secure parking. "
                     "Short and long stays. Book direct.",
        "hero_video": "media/lakeside-tour.mp4",
        "hero_poster": "media/exterior-frontage.jpg",
        "map": "https://www.google.com/maps?q=Lakeside%20Estate%2C%20Accra%2C%20Ghana&z=14&output=embed",
        "stats": [("12", "Serviced apartments"), ("Full", "Kitchens"), ("24/7", "Security"), ("Free", "Parking")],
        "about_image": "media/exterior-approach.jpg",
        "about_image_alt": "The frontage of Gateway Lodge Lakeside, Lakeside Estate, Accra",
        "about_kicker": "The Lodge",
        "about_head": "The largest of the three, and the calmest.",
        "about_body": [
            "Lakeside sits east of the centre in the Lakeside Estate, far enough out that the "
            "evenings are quiet and close enough that the city is still a drive rather than a "
            "journey. Twelve serviced apartments in one gated block, each with its own kitchen, "
            "laundry and living room.",
            "It is the property guests choose when they are travelling as a family, when a team "
            "needs several apartments under one roof, or when the point of the trip is to slow "
            "down for a few days.",
        ],
        "pillars": [
            ("Room for a group", "Twelve apartments means a family, a team or a small event fits in one place."),
            ("Genuinely quiet", "A gated residential estate, rather than a main-road frontage."),
            ("Properly self-contained", "A full kitchen and laundry in every apartment, not a kitchenette."),
        ],
        "rooms": [
            ("media/bedroom-suite.jpg", "Suite bedroom at Gateway Lodge Lakeside",
             "Lakeside Suite", "The largest category: a king bed, a separate seating area and a full bathroom."),
            ("media/bedroom-twin.jpg", "Twin bedroom at Gateway Lodge Lakeside",
             "Twin Room", "Two beds and a quiet outlook, for colleagues travelling together or a family."),
            ("media/living-open.jpg", "Open-plan living area at Gateway Lodge Lakeside",
             "Serviced Unit", "A full kitchen, a living area and a desk, built for stays measured in weeks."),
            ("media/bedroom-double.jpg", "Double bedroom at Gateway Lodge Lakeside",
             "Double Room", "A comfortable double with everything a short stay needs, nothing it does not."),
        ],
        "facilities_image": "media/kitchen-island.jpg",
        "facilities_image_alt": "Fitted kitchen and island at Gateway Lodge Lakeside",
        "facilities_head": "A lodge that runs properly, every day",
        "facilities_body": "Twelve apartments is the size where service either holds or slips. Lakeside is "
                           "staffed to hold it: a gate that is always manned, standby power that "
                           "picks up without anyone asking, and housekeeping that does not need chasing.",
        "facilities": [
            ("kitchen", "Full Kitchen", "Cooker, oven, fridge and full worktop in every apartment."),
            ("wifi", "High-Speed Wi-Fi", "Covered across the apartments and the common areas."),
            ("ac", "Air Conditioning", "In every room, individually controlled."),
            ("laundry", "In-Apartment Laundry", "A washing machine in the kitchen of each apartment."),
            ("housekeeping", "Daily Housekeeping", "Apartments serviced every day, linen changed on schedule."),
            ("power", "Backup Power", "Standby generation so the property stays lit and cool."),
            ("tv", "Smart Television", "A mounted television in each living room."),
            ("parking", "Ample Parking", "On-site parking for guests, at no extra charge."),
            ("security", "24-Hour Security", "Manned gate and a walled, gated block."),
            ("water", "Balconies", "A private balcony to most apartments, above the estate."),
            ("family", "Family Friendly", "Connecting arrangements and extra beds available."),
            ("concierge", "Long-Stay Rates", "Weekly and monthly terms for stays measured in months."),
        ],
        "gallery": [
            ("media/exterior-frontage.jpg", "The frontage of Gateway Lodge Lakeside"),
            ("media/living-lounge.jpg", "Living room at Gateway Lodge Lakeside"),
            ("media/kitchen-wide.jpg", "Fitted kitchen at Gateway Lodge Lakeside"),
            ("media/bedroom-king.jpg", "King bedroom at Gateway Lodge Lakeside"),
            ("media/living-tv.jpg", "Living room and television wall at Gateway Lodge Lakeside"),
            ("media/balcony.jpg", "Balcony at Gateway Lodge Lakeside"),
            ("media/kitchen-laundry.jpg", "Kitchen and laundry at Gateway Lodge Lakeside"),
            ("media/bathroom.jpg", "Bathroom at Gateway Lodge Lakeside"),
            ("media/bedroom-tv.jpg", "Bedroom at Gateway Lodge Lakeside"),
            ("media/walkway.jpg", "Walkway at Gateway Lodge Lakeside"),
            ("media/bathroom-shower.jpg", "Shower room at Gateway Lodge Lakeside"),
            ("media/exterior-approach.jpg", "Approach to Gateway Lodge Lakeside"),
        ],
        "location_points": [
            "Set inside Lakeside Estate, away from the main-road noise",
            "Around 40 minutes by car to Kotoka International Airport",
            "Easy reach of the Accra&ndash;Aburi road and the eastern suburbs",
            "Shops, pharmacies and fuel within a few minutes&rsquo; drive",
        ],
        "phone": "+233 24 000 0000",
        "email": "reservations@gatewaylodgegroup.com",
    },
    {
        "slug": "tamale",
        "name": "Gateway Lodge Tamale",
        "short": "Tamale",
        "domain": "tamale.gatewaylodgegroup.com",
        "locality": "Tamale, Northern Region",
        "region": "Northern Region",
        "address": "Tamale, Northern Region, Ghana",
        "units": "6 Units",
        "tagline": "Six fully furnished apartments in Tamale, built for the way people actually "
                   "work in the north, for a night, a week, or a whole season.",
        "meta_desc": "Gateway Lodge Tamale offers six fully furnished one and two-bedroom serviced "
                     "apartments in Tamale, Northern Region, for short and long stays, secure "
                     "parking and full facilities. Book direct.",
        "hero_video": "media/tamale-tour.mp4",
        "hero_poster": "media/hero-courtyard.jpg",
        "map": "https://www.google.com/maps?q=Tamale%2C%20Northern%20Region%2C%20Ghana&z=13&output=embed",
        "stats": [("6", "Furnished apartments"), ("1 &amp; 2", "Bedroom layouts"), ("Short &amp; long", "Stays"), ("24/7", "Security")],
        "about_image": "media/exterior-frontage.jpg",
        "about_image_alt": "Frontage and boundary wall at Gateway Lodge Tamale",
        "about_kicker": "The Apartments",
        "about_head": "A base in the north, not a room for the night.",
        "about_body": [
            "Tamale is the working capital of northern Ghana, and most people who come here come "
            "for a stretch: a project, a posting, a season of fieldwork. Gateway Lodge Tamale is "
            "built for exactly that: six fully furnished apartments in a purpose-built, "
            "walled block, in one and two-bedroom layouts.",
            "Each apartment has its own kitchen, its own living room and its own bathroom, so a "
            "long stay does not mean living out of a suitcase. Housekeeping, laundry, secure "
            "parking and a manned gate come with it.",
        ],
        "pillars": [
            ("Made for the long stay", "Full kitchens, laundry and living space in every apartment."),
            ("One or two bedrooms", "Sized for a single traveller, a colleague pair or a family."),
            ("Purpose-built and walled", "A gated block with its own courtyard parking, not a converted house."),
        ],
        "rooms": [
            ("media/bedroom-king.jpg", "Bedroom with teal curtains at Gateway Lodge Tamale",
             "One-Bedroom Apartment", "A double bedroom, a full living room, kitchen and bathroom, for one or two guests."),
            ("media/living-lounge.jpg", "Living room with sofas and coffee table at Gateway Lodge Tamale",
             "Two-Bedroom Apartment", "Two bedrooms off a shared living room, the layout families and pairs of colleagues take."),
            ("media/kitchen.jpg", "Fitted kitchen with cooker and washing machine at Gateway Lodge Tamale",
             "Private Kitchen", "Cooker, fridge, microwave, washing machine and worktop space in every apartment."),
            ("media/terrace.jpg", "Shaded terrace seating at Gateway Lodge Tamale",
             "Shared Terrace", "A covered terrace off the upper walkway, for the hours after the heat drops."),
        ],
        "facilities_image": "media/living-tv.jpg",
        "facilities_image_alt": "Living room with television and ceiling fan at Gateway Lodge Tamale",
        "facilities_head": "Set up for a working stay in the north",
        "facilities_body": "Everything an extended stay in Tamale asks for is already fitted: power "
                           "that holds through an outage, cooling in every room, a kitchen you can "
                           "cook a real meal in, and parking behind a gate.",
        "facilities": [
            ("kitchen", "Private Kitchen", "Cooker, fridge, microwave and full worktop in every apartment."),
            ("laundry", "Washing Machine", "In-apartment laundry, plus a pressing service on request."),
            ("ac", "Air Conditioning &amp; Fans", "Split units and ceiling fans in the bedrooms and living rooms."),
            ("power", "Backup Power", "Standby generation covering the whole block."),
            ("wifi", "Wi-Fi", "Internet across the apartments and the common areas."),
            ("tv", "Smart Television", "A mounted television in each living room."),
            ("parking", "Gated Parking", "Courtyard parking inside the walls, with a manned gate."),
            ("security", "24-Hour Security", "A walled compound with security on site day and night."),
            ("housekeeping", "Housekeeping", "Scheduled cleaning and fresh linen, at a rhythm that suits your stay."),
            ("water", "Water Storage", "Stored and pumped supply, so the taps do not depend on the mains."),
            ("balcony", "Terrace &amp; Walkway", "Shaded outdoor seating on the upper level."),
            ("airport", "Airport Runs", "Transfers to and from Tamale Airport arranged on request."),
        ],
        "gallery": [
            ("media/hero-courtyard.jpg", "Courtyard and two-storey frontage at Gateway Lodge Tamale"),
            ("media/living-lounge.jpg", "Living room seating at Gateway Lodge Tamale"),
            ("media/bedroom-king.jpg", "Bedroom with wooden headboard at Gateway Lodge Tamale"),
            ("media/kitchen-wide.jpg", "Kitchen with washing machine and cooker at Gateway Lodge Tamale"),
            ("media/living-open.jpg", "Open living room with chandelier at Gateway Lodge Tamale"),
            ("media/bedroom-tv.jpg", "Bedroom with wall-mounted television at Gateway Lodge Tamale"),
            ("media/bathroom.jpg", "Bathroom with glass shower at Gateway Lodge Tamale"),
            ("media/dining-nook.jpg", "Dining table for two at Gateway Lodge Tamale"),
            ("media/terrace.jpg", "Covered terrace at Gateway Lodge Tamale"),
            ("media/exterior-street.jpg", "Street view of Gateway Lodge Tamale"),
            ("media/stairwell.jpg", "Stairwell to the upper apartments at Gateway Lodge Tamale"),
            ("media/entrance-gate.jpg", "Signage and entrance gate at Gateway Lodge Tamale"),
        ],
        "location_points": [
            "In Tamale, within reach of the central business area and the markets",
            "A short drive from Tamale International Airport",
            "Convenient for Northern Region project sites and NGO offices",
            "Banks, fuel and supermarkets close by",
        ],
        "phone": "+233 24 000 0000",
        "email": "reservations@gatewaylodgegroup.com",
    },
]

CSS = """/* Gateway Lodge Group — property subdomain landing pages.
   Self-contained: this file plus index.html and media/ is the whole site. */

:root {
  --maroon-900: #4a1119;
  --maroon-700: #5c1620;
  --maroon-600: #7a1e2b;
  --gold-500:   #dba845;
  --gold-300:   #ecc989;
  --blue-700:   #1f3a5f;
  --cream:      #f7f3ee;
  --sand:       #ece3d8;
  --page:       #fbf9f6;
  --white:      #ffffff;
  --ink-900:    #201a17;
  --ink-600:    #5a5049;
  --ink-400:    #8a8078;
  --border:     #e6ddd3;

  --font-display: Georgia, 'Iowan Old Style', 'Palatino Linotype', serif;
  --font-body: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
  --fs-h1: clamp(2.5rem, 6vw, 4.6rem);
  --fs-h2: clamp(1.85rem, 3.6vw, 2.9rem);

  --space-2: 1rem;
  --space-3: 1.5rem;
  --space-4: 2.5rem;
  --space-5: 4.5rem;
  --space-6: 7rem;
  --max-width: 1240px;
  --header-h: 76px;
}

*, *::before, *::after { box-sizing: border-box; }
html { scroll-behavior: smooth; scroll-padding-top: var(--header-h); }
body {
  margin: 0;
  font-family: var(--font-body);
  color: var(--ink-900);
  background: var(--page);
  line-height: 1.75;
  -webkit-font-smoothing: antialiased;
}
img, video, iframe { max-width: 100%; display: block; }
a { color: inherit; text-decoration: none; }
ul { list-style: none; margin: 0; padding: 0; }
h1, h2, h3 {
  font-family: var(--font-display);
  color: var(--maroon-700);
  margin: 0 0 var(--space-2);
  font-weight: 400;
  line-height: 1.18;
  letter-spacing: -0.005em;
}
h1 { font-size: var(--fs-h1); }
h2 { font-size: var(--fs-h2); }
h3 { font-size: 1.2rem; }
p { margin: 0 0 var(--space-2); color: var(--ink-600); }
button, input, select, textarea { font-family: inherit; font-size: 1rem; }

.container { width: min(100% - 3rem, var(--max-width)); margin-inline: auto; }
.section { padding: var(--space-6) 0; }
.section-alt { background: var(--cream); }
.section-dark { background: var(--maroon-700); }
.section-dark h2, .section-dark p { color: var(--white); }
.section-dark .eyebrow { color: var(--gold-300); }

.eyebrow {
  display: block;
  font-size: 0.72rem;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--maroon-600);
  margin-bottom: 0.75rem;
}
.divider { width: 46px; height: 2px; background: var(--gold-500); margin-bottom: var(--space-3); }
.section-head { max-width: 62ch; margin-bottom: var(--space-4); }
.section-head.center { margin-inline: auto; text-align: center; }
.section-head.center .divider { margin-inline: auto; }
.lede { font-size: 1.08rem; }

.skip-link {
  position: absolute; left: -999px; top: 0; z-index: 200;
  background: var(--maroon-700); color: var(--white); padding: 0.7rem 1.1rem;
}
.skip-link:focus { left: 0; }

/* ---------- Buttons ---------- */
.btn {
  display: inline-block;
  padding: 0.95rem 1.9rem;
  font-size: 0.76rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  border: 1px solid transparent;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}
.btn-gold { background: var(--gold-500); color: var(--maroon-900); }
.btn-gold:hover, .btn-gold:focus-visible { background: var(--gold-300); }
.btn-ghost { border-color: rgba(255,255,255,0.65); color: var(--white); }
.btn-ghost:hover, .btn-ghost:focus-visible { background: var(--white); color: var(--maroon-700); }
.btn-outline { border-color: var(--maroon-700); color: var(--maroon-700); }
.btn-outline:hover, .btn-outline:focus-visible { background: var(--maroon-700); color: var(--white); }
.btn-row { display: flex; flex-wrap: wrap; gap: var(--space-2); }

/* ---------- Header ---------- */
.site-header {
  position: fixed; inset: 0 0 auto 0; z-index: 100;
  background: transparent;
  transition: background 0.3s ease, box-shadow 0.3s ease;
}
.site-header.is-solid {
  background: var(--white);
  box-shadow: 0 1px 0 var(--border);
}
.header-inner {
  display: flex; align-items: center; gap: var(--space-3);
  height: var(--header-h);
}
.brand { display: flex; align-items: center; gap: 0.7rem; margin-right: auto; }
.brand-lockup { display: flex; flex-direction: column; line-height: 1.15; }
.brand-group {
  font-size: 0.6rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--white); opacity: 0.85;
}
.brand-property {
  font-family: var(--font-display); font-size: 1.06rem; color: var(--white);
}
.is-solid .brand-group { color: var(--ink-400); opacity: 1; }
.is-solid .brand-property { color: var(--maroon-700); }

.primary-nav ul { display: flex; gap: 1.6rem; align-items: center; }
.primary-nav a {
  font-size: 0.72rem; letter-spacing: 0.16em; text-transform: uppercase;
  color: var(--white); padding: 0.4rem 0; border-bottom: 1px solid transparent;
}
.primary-nav a:hover, .primary-nav a:focus-visible { border-bottom-color: var(--gold-500); }
.is-solid .primary-nav a { color: var(--ink-900); }

.header-actions { display: flex; align-items: center; gap: var(--space-2); }
.btn-book {
  padding: 0.8rem 1.5rem; background: var(--gold-500); color: var(--maroon-900);
  font-size: 0.72rem; letter-spacing: 0.16em; text-transform: uppercase;
}
.btn-book:hover, .btn-book:focus-visible { background: var(--gold-300); }

.nav-toggle { display: none; }
.nav-toggle-label {
  display: none; width: 42px; height: 42px; align-items: center; justify-content: center;
  cursor: pointer;
}
.burger, .burger::before, .burger::after {
  display: block; width: 22px; height: 1.5px; background: var(--white); content: '';
}
.burger { position: relative; }
.burger::before { position: absolute; top: -7px; }
.burger::after { position: absolute; top: 7px; }
.is-solid .burger, .is-solid .burger::before, .is-solid .burger::after { background: var(--ink-900); }

/* ---------- Hero ---------- */
.hero {
  position: relative; min-height: 100svh;
  display: flex; align-items: flex-end;
  padding: calc(var(--header-h) + var(--space-4)) 0 var(--space-6);
  overflow: hidden;
  background: var(--maroon-900);
}
.hero-media {
  position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
}
.hero::after {
  content: ''; position: absolute; inset: 0;
  background:
    linear-gradient(90deg, rgba(28,11,13,0.78) 0%, rgba(28,11,13,0.45) 42%, rgba(28,11,13,0.1) 72%),
    linear-gradient(180deg, rgba(28,11,13,0.6) 0%, rgba(28,11,13,0.2) 30%, rgba(28,11,13,0.85) 100%);
}
.hero-content { position: relative; z-index: 2; max-width: 42ch; text-shadow: 0 1px 24px rgba(20,8,10,0.5); }
.hero-content .eyebrow { color: var(--gold-300); }
.hero-content h1 { color: var(--white); margin-bottom: var(--space-3); }
.hero-content p { color: rgba(255,255,255,0.9); font-size: 1.1rem; }
.hero-meta {
  display: flex; flex-wrap: wrap; gap: 1.4rem; margin-bottom: var(--space-3);
  font-size: 0.74rem; letter-spacing: 0.16em; text-transform: uppercase;
  color: var(--gold-300);
}
.hero-scroll {
  position: absolute; left: 50%; bottom: 1.4rem; transform: translateX(-50%); z-index: 2;
  font-size: 0.64rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: rgba(255,255,255,0.7);
}

/* ---------- Stat bar ---------- */
.stat-bar { background: var(--maroon-700); color: var(--white); }
.stat-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  border-left: 1px solid rgba(255,255,255,0.14);
}
.stat {
  padding: var(--space-4) var(--space-3);
  border-right: 1px solid rgba(255,255,255,0.14);
  text-align: center;
}
.stat b { display: block; font-family: var(--font-display); font-size: 1.9rem; font-weight: 400; color: var(--gold-300); }
.stat span { font-size: 0.68rem; letter-spacing: 0.18em; text-transform: uppercase; opacity: 0.85; }

/* ---------- Split ---------- */
.split { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-5); align-items: center; }
.split.reverse .split-media { order: 2; }
.split-media img { width: 100%; height: auto; object-fit: cover; aspect-ratio: 4 / 3; }

.pillars { display: grid; gap: var(--space-3); margin-top: var(--space-3); }
.pillar { border-left: 2px solid var(--gold-500); padding-left: var(--space-3); }
.pillar h3 { margin-bottom: 0.3rem; font-size: 1.05rem; }
.pillar p { margin: 0; font-size: 0.95rem; }

/* ---------- Cards ---------- */
.card-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-3); }
.card { background: var(--white); border: 1px solid var(--border); display: flex; flex-direction: column; }
.card img { aspect-ratio: 4 / 3; height: auto; object-fit: cover; width: 100%; }
.card-body { padding: var(--space-3); }
.card-body h3 { margin-bottom: 0.4rem; }
.card-body p { margin: 0; font-size: 0.94rem; }

/* ---------- Facilities ---------- */
.facility-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 1px; background: var(--border); border: 1px solid var(--border);
  margin-top: var(--space-4);
}
.facility { background: var(--page); padding: var(--space-3); display: flex; gap: 0.9rem; }
.section-alt .facility { background: var(--cream); }
.facility svg { flex: 0 0 auto; color: var(--gold-500); margin-top: 0.35rem; }
.facility h3 { font-family: var(--font-body); font-size: 0.86rem; letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--maroon-700); margin-bottom: 0.25rem; }
.facility p { margin: 0; font-size: 0.92rem; line-height: 1.6; }

/* ---------- Gallery ---------- */
.gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
.gallery-grid button {
  padding: 0; border: 0; background: none; cursor: zoom-in; display: block; overflow: hidden;
  aspect-ratio: 4 / 3;
}
.gallery-grid img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.5s ease;
}
.gallery-grid button:hover img, .gallery-grid button:focus-visible img { transform: scale(1.05); }
.gallery-grid button:nth-child(1), .gallery-grid button:nth-child(8) {
  grid-column: span 2; grid-row: span 2; aspect-ratio: auto;
}

.lightbox {
  position: fixed; inset: 0; z-index: 200; display: none;
  background: rgba(24,12,14,0.94); padding: var(--space-4);
  align-items: center; justify-content: center;
}
.lightbox[open], .lightbox.is-open { display: flex; }
.lightbox img { max-height: 86vh; max-width: 100%; object-fit: contain; }
.lightbox-close {
  position: absolute; top: 1rem; right: 1.25rem; background: none; border: 0;
  color: var(--white); font-size: 2rem; line-height: 1; cursor: pointer;
}
.lightbox-nav {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: none; border: 0; color: var(--white); font-size: 2.4rem; cursor: pointer;
  padding: 0.5rem 1rem;
}
.lightbox-prev { left: 0.5rem; }
.lightbox-next { right: 0.5rem; }

/* ---------- Location & contact ---------- */
.contact-grid { display: grid; grid-template-columns: 1.05fr 1fr; gap: var(--space-5); }
.map-embed { aspect-ratio: 4 / 3; }
.map-embed iframe { width: 100%; height: 100%; border: 0; }
.icon-list li { display: flex; gap: 0.7rem; align-items: flex-start; margin-bottom: 0.6rem; color: var(--ink-600); }
.icon-list svg { flex: 0 0 auto; color: var(--gold-500); margin-top: 0.45rem; }

.contact-details { display: grid; gap: var(--space-3); margin-bottom: var(--space-4); }
.contact-details dt {
  font-size: 0.68rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-400);
}
.contact-details dd { margin: 0.2rem 0 0; font-size: 1.02rem; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-2); }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.68rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-400); }
.field input, .field select, .field textarea {
  padding: 0.85rem 0.95rem; border: 1px solid var(--border); background: var(--white);
  color: var(--ink-900);
}
.field textarea { min-height: 130px; resize: vertical; }
.field input:focus, .field select:focus, .field textarea:focus {
  outline: 2px solid var(--gold-500); outline-offset: -2px;
}
.form-note { font-size: 0.85rem; color: var(--ink-400); }

/* ---------- Booking band ---------- */
.book-band { text-align: center; }
.book-band .container { max-width: 74ch; }
.book-band .divider { margin-inline: auto; }
.book-band .btn-row { justify-content: center; }
.book-note { font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-top: var(--space-3); }

/* ---------- Footer ---------- */
.site-footer { background: var(--maroon-900); color: rgba(255,255,255,0.8); padding: var(--space-5) 0 var(--space-3); }
.footer-top { display: grid; grid-template-columns: 1.6fr 1fr 1fr; gap: var(--space-4); }
.site-footer h3 {
  color: var(--white); font-family: var(--font-body); font-size: 0.72rem;
  letter-spacing: 0.18em; text-transform: uppercase; margin-bottom: var(--space-2);
}
.site-footer a:hover, .site-footer a:focus-visible { color: var(--gold-300); }
.site-footer li { margin-bottom: 0.4rem; font-size: 0.94rem; }
.site-footer p { color: rgba(255,255,255,0.7); font-size: 0.94rem; }
.footer-brand .brand-property, .footer-brand .brand-group { color: var(--white); }
.social-icons { display: flex; gap: 0.6rem; margin-top: var(--space-2); }
.social-icons a {
  width: 36px; height: 36px; display: grid; place-items: center;
  border: 1px solid rgba(255,255,255,0.25); color: var(--white);
}
.social-icons a:hover { border-color: var(--gold-500); color: var(--gold-300); }
.footer-bottom {
  display: flex; flex-wrap: wrap; gap: var(--space-2); justify-content: space-between;
  border-top: 1px solid rgba(255,255,255,0.15); margin-top: var(--space-4); padding-top: var(--space-3);
  font-size: 0.85rem;
}
.footer-bottom ul { display: flex; gap: var(--space-2); }
.footer-bottom li { margin: 0; }

/* ---------- Mobile book bar ---------- */
.mobile-book {
  display: none; position: fixed; inset: auto 0 0 0; z-index: 90;
  background: var(--maroon-700); padding: 0.6rem 1rem;
  box-shadow: 0 -8px 24px -18px rgba(0,0,0,0.6);
}
.mobile-book a { display: block; text-align: center; }

/* ---------- Responsive ---------- */
@media (max-width: 1024px) {
  .card-grid { grid-template-columns: repeat(2, 1fr); }
  .facility-grid { grid-template-columns: repeat(2, 1fr); }
  .stat-grid { grid-template-columns: repeat(2, 1fr); }
  .gallery-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 860px) {
  :root { --space-6: 4.5rem; --space-5: 3rem; }
  .nav-toggle-label { display: flex; }
  .primary-nav {
    position: fixed; inset: 0 0 0 auto; width: min(320px, 84vw);
    background: var(--white); padding: calc(var(--header-h) + 1rem) var(--space-3) var(--space-3);
    transform: translateX(100%); transition: transform 0.3s ease; z-index: 110;
    box-shadow: -20px 0 40px -30px rgba(0,0,0,0.5);
  }
  .primary-nav ul { flex-direction: column; align-items: flex-start; gap: 0.4rem; }
  .primary-nav a { color: var(--ink-900); padding: 0.7rem 0; font-size: 0.82rem; }
  .nav-toggle:checked ~ .primary-nav { transform: translateX(0); }
  .nav-backdrop {
    position: fixed; inset: 0; background: rgba(24,12,14,0.5); z-index: 105;
    opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
  }
  .nav-toggle:checked ~ .nav-backdrop { opacity: 1; pointer-events: auto; }
  .header-actions .btn-book { display: none; }
  .split, .contact-grid { grid-template-columns: 1fr; gap: var(--space-4); }
  .split.reverse .split-media { order: 0; }
  .footer-top { grid-template-columns: 1fr 1fr; }
  .mobile-book { display: block; }
  body { padding-bottom: 64px; }
  .hero { min-height: 88svh; }
}

@media (max-width: 620px) {
  .container { width: min(100% - 2rem, var(--max-width)); }
  .card-grid, .facility-grid, .gallery-grid, .form-grid { grid-template-columns: 1fr; }
  .gallery-grid button:nth-child(1), .gallery-grid button:nth-child(8) {
    grid-column: span 1; grid-row: span 1; aspect-ratio: 4 / 3;
  }
  .footer-top { grid-template-columns: 1fr; }
  .brand-property { font-size: 0.95rem; }
}

@media (prefers-reduced-motion: reduce) {
  html { scroll-behavior: auto; }
  *, *::before, *::after { transition-duration: 0.01ms !important; animation-duration: 0.01ms !important; }
}
"""

JS = """(function () {
  var header = document.querySelector('.site-header');
  var hero = document.querySelector('.hero');
  var toggle = document.getElementById('nav-toggle');

  function onScroll() {
    var past = window.scrollY > (hero ? hero.offsetHeight - 120 : 80);
    header.classList.toggle('is-solid', past || (toggle && toggle.checked));
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Close the mobile drawer after tapping a link.
  document.querySelectorAll('.primary-nav a, .nav-backdrop').forEach(function (el) {
    el.addEventListener('click', function () {
      if (toggle) { toggle.checked = false; }
      onScroll();
    });
  });

  // Gallery lightbox.
  var box = document.querySelector('.lightbox');
  if (!box) { return; }
  var boxImg = box.querySelector('img');
  var buttons = Array.prototype.slice.call(document.querySelectorAll('.gallery-grid button'));
  var index = 0;

  function show(i) {
    index = (i + buttons.length) % buttons.length;
    var source = buttons[index].querySelector('img');
    boxImg.src = source.src;
    boxImg.alt = source.alt;
  }
  function open(i) {
    show(i);
    box.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    box.querySelector('.lightbox-close').focus();
  }
  function close() {
    box.classList.remove('is-open');
    document.body.style.overflow = '';
    buttons[index].focus();
  }

  buttons.forEach(function (b, i) { b.addEventListener('click', function () { open(i); }); });
  box.querySelector('.lightbox-close').addEventListener('click', close);
  box.querySelector('.lightbox-prev').addEventListener('click', function () { show(index - 1); });
  box.querySelector('.lightbox-next').addEventListener('click', function () { show(index + 1); });
  box.addEventListener('click', function (e) { if (e.target === box) { close(); } });
  document.addEventListener('keydown', function (e) {
    if (!box.classList.contains('is-open')) { return; }
    if (e.key === 'Escape') { close(); }
    if (e.key === 'ArrowLeft') { show(index - 1); }
    if (e.key === 'ArrowRight') { show(index + 1); }
  });
})();
"""


def brand(p, footer=False):
    href = "#home" if not footer else "#home"
    return (
        f'<a class="brand" href="{href}">{LOGO}'
        '<span class="brand-lockup">'
        '<span class="brand-group">Gateway Lodge Group</span>'
        f'<span class="brand-property">{p["short"]}</span>'
        "</span></a>"
    )


def hero_media(p):
    if p["hero_video"]:
        return (
            f'<video class="hero-media" autoplay muted loop playsinline preload="metadata" '
            f'poster="{p["hero_poster"]}">'
            f'<source src="{p["hero_video"]}" type="video/mp4">'
            "</video>"
        )
    return (
        f'<img class="hero-media" src="{p["hero_poster"]}" '
        f'alt="{p["name"]}, {p["locality"]}" fetchpriority="high">'
    )


def build(p):
    out = os.path.join(ROOT, p["slug"])
    os.makedirs(out, exist_ok=True)
    url = f'https://{p["domain"]}/'
    og = f'{url}{p["hero_poster"]}'

    stats = "".join(
        f'<div class="stat"><b>{v}</b><span>{k}</span></div>' for v, k in p["stats"]
    )
    pillars = "".join(
        f'<div class="pillar"><h3>{h}</h3><p>{b}</p></div>' for h, b in p["pillars"]
    )
    about_body = "".join(f"<p>{t}</p>" for t in p["about_body"])
    rooms = "".join(
        f'<article class="card"><img src="{src}" alt="{alt}" loading="lazy" width="1080" height="810">'
        f'<div class="card-body"><h3>{title}</h3><p>{body}</p></div></article>'
        for src, alt, title, body in p["rooms"]
    )
    facilities = "".join(
        f'<div class="facility"><svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">'
        f'{ICONS[icon]}</svg><div><h3>{title}</h3><p>{body}</p></div></div>'
        for icon, title, body in p["facilities"]
    )
    gallery = "".join(
        f'<button type="button" aria-label="Open image: {alt}">'
        f'<img src="{src}" alt="{alt}" loading="lazy" width="1080" height="810"></button>'
        for src, alt in p["gallery"]
    )
    location_points = "".join(f"<li>{CHECK}{t}</li>" for t in p["location_points"])
    socials = "".join(
        f'<a href="{href}" aria-label="{p["name"]} on {label}">'
        f'<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">{path}</svg></a>'
        for label, href, path in SOCIALS
    )
    whatsapp = "https://wa.me/" + p["phone"].replace(" ", "").replace("+", "")
    tel = "tel:" + p["phone"].replace(" ", "")

    html = f"""<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{p["name"]} | {p["locality"]} | Gateway Lodge Group</title>
<meta name="description" content="{p["meta_desc"]}">
<link rel="canonical" href="{url}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Gateway Lodge Group">
<meta property="og:title" content="{p["name"]} | {p["locality"]}">
<meta property="og:description" content="{p["meta_desc"]}">
<meta property="og:image" content="{og}">
<meta property="og:url" content="{url}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{p["name"]} | {p["locality"]}">
<meta name="twitter:description" content="{p["meta_desc"]}">
<meta name="twitter:image" content="{og}">
<meta name="theme-color" content="#5c1620">
<link rel="icon" href="{FAVICON}">
<link rel="stylesheet" href="landing.css">
<script type="application/ld+json">
{{
  "@context": "https://schema.org",
  "@type": "LodgingBusiness",
  "name": "{p["name"]}",
  "description": "{p["meta_desc"].replace("&mdash;", ",").replace("&rsquo;", "’")}",
  "url": "{url}",
  "image": "{og}",
  "telephone": "{p["phone"]}",
  "email": "{p["email"]}",
  "address": {{
    "@type": "PostalAddress",
    "streetAddress": "{p["address"]}",
    "addressLocality": "{p["locality"].split(",")[0]}",
    "addressRegion": "{p["region"]}",
    "addressCountry": "GH"
  }},
  "parentOrganization": {{
    "@type": "Organization",
    "name": "Gateway Lodge Group",
    "url": "{GROUP}/"
  }}
}}
</script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
  <div class="container header-inner">
    {brand(p)}
    <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-hidden="true">
    <nav class="primary-nav" aria-label="Primary">
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#facilities">Facilities</a></li>
        <li><a href="#gallery">Gallery</a></li>
        <li><a href="#contact">Contact</a></li>
        <li><a href="{GROUP}/">Gateway Lodge Group</a></li>
      </ul>
    </nav>
    <label for="nav-toggle" class="nav-backdrop" aria-hidden="true"></label>
    <div class="header-actions">
      <a href="#book" class="btn-book">Book Now</a>
      <label for="nav-toggle" class="nav-toggle-label" role="button" tabindex="0" aria-label="Open menu">
        <span class="burger" aria-hidden="true"></span>
      </label>
    </div>
  </div>
</header>

<main id="main">

<section class="hero" id="home">
  {hero_media(p)}
  <div class="container hero-content">
    <span class="eyebrow">Gateway Lodge Group</span>
    <h1>{p["name"]}</h1>
    <div class="hero-meta">
      <span>{p["locality"]}</span>
      <span>{p["units"]}</span>
    </div>
    <p>{p["tagline"]}</p>
    <div class="btn-row">
      <a class="btn btn-gold" href="#book">Book Now</a>
      <a class="btn btn-ghost" href="#about">Explore the property</a>
    </div>
  </div>
  <span class="hero-scroll" aria-hidden="true">Scroll</span>
</section>

<section class="stat-bar">
  <div class="container">
    <div class="stat-grid">{stats}</div>
  </div>
</section>

<section class="section" id="about">
  <div class="container split">
    <div class="split-media">
      <img src="{p["about_image"]}" alt="{p["about_image_alt"]}" loading="lazy" width="1080" height="810">
    </div>
    <div class="split-body">
      <span class="eyebrow">{p["about_kicker"]}</span>
      <div class="divider"></div>
      <h2>{p["about_head"]}</h2>
      {about_body}
      <div class="pillars">{pillars}</div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Accommodation</span>
      <div class="divider"></div>
      <h2>Where you will stay</h2>
      <p class="lede">Rates, availability and the full description of each category are on the booking page.</p>
    </div>
    <div class="card-grid">{rooms}</div>
  </div>
</section>

<section class="section" id="facilities">
  <div class="container split reverse">
    <div class="split-media">
      <img src="{p["facilities_image"]}" alt="{p["facilities_image_alt"]}" loading="lazy" width="1080" height="810">
    </div>
    <div class="split-body">
      <span class="eyebrow">Facilities &amp; Amenities</span>
      <div class="divider"></div>
      <h2>{p["facilities_head"]}</h2>
      <p>{p["facilities_body"]}</p>
    </div>
  </div>
  <div class="container">
    <div class="facility-grid">{facilities}</div>
  </div>
</section>

<section class="section section-alt" id="gallery">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Gallery</span>
      <div class="divider"></div>
      <h2>A closer look</h2>
    </div>
    <div class="gallery-grid">{gallery}</div>
  </div>
</section>

<section class="section" id="contact">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Location &amp; Contact</span>
      <div class="divider"></div>
      <h2>Find us, or just ask</h2>
    </div>
    <div class="contact-grid">
      <div>
        <div class="map-embed">
          <iframe src="{p["map"]}" title="Map showing {p["name"]}, {p["locality"]}"
            loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </div>
        <dl class="contact-details" style="margin-top: var(--space-3)">
          <div><dt>Address</dt><dd>{p["address"]}</dd></div>
          <div><dt>Reservations</dt><dd><a href="{tel}">{p["phone"]}</a> &middot;
            <a href="{whatsapp}">WhatsApp</a></dd></div>
          <div><dt>Email</dt><dd><a href="mailto:{p["email"]}">{p["email"]}</a></dd></div>
        </dl>
        <ul class="icon-list">{location_points}</ul>
      </div>
      <div>
        <h3>Send an enquiry</h3>
        <p>Tell us the dates and how many of you there are, and reservations will come back with
          availability and a rate.</p>
        <form class="form-grid" action="https://formsubmit.co/{p["email"]}" method="POST">
          <input type="hidden" name="_subject" value="{p["name"]} enquiry from {p["domain"]}">
          <input type="hidden" name="Property" value="{p["name"]}">
          <input type="text" name="_honey" style="display:none" tabindex="-1" autocomplete="off">
          <div class="field"><label for="name">Name</label>
            <input id="name" name="Name" type="text" required autocomplete="name"></div>
          <div class="field"><label for="email">Email</label>
            <input id="email" name="Email" type="email" required autocomplete="email"></div>
          <div class="field"><label for="phone">Phone</label>
            <input id="phone" name="Phone" type="tel" autocomplete="tel"></div>
          <div class="field"><label for="guests">Guests</label>
            <input id="guests" name="Guests" type="number" min="1" max="20" value="2"></div>
          <div class="field"><label for="checkin">Check-in</label>
            <input id="checkin" name="Check-in" type="date"></div>
          <div class="field"><label for="checkout">Check-out</label>
            <input id="checkout" name="Check-out" type="date"></div>
          <div class="field full"><label for="enquiry">Enquiry type</label>
            <select id="enquiry" name="Enquiry type">
              <option>Reservation</option>
              <option>Long stay</option>
              <option>Corporate booking</option>
              <option>Event or group</option>
              <option>General enquiry</option>
            </select></div>
          <div class="field full"><label for="message">Message</label>
            <textarea id="message" name="Message"></textarea></div>
          <div class="field full">
            <button class="btn btn-outline" type="submit">Send enquiry</button>
            <p class="form-note">Or message reservations on WhatsApp for a same-day reply.</p>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<section class="section section-dark book-band" id="book">
  <div class="container">
    <span class="eyebrow">Ready when you are</span>
    <div class="divider"></div>
    <h2>Book {p["name"]}</h2>
    <p>Book direct for the best available rate. Reservations answer by phone and WhatsApp every
      day, or send the enquiry form and we will come back to you.</p>
    <div class="btn-row">
      <a class="btn btn-gold" href="{whatsapp}">Book Now</a>
      <a class="btn btn-ghost" href="{tel}">Call reservations</a>
      <a class="btn btn-ghost" href="#contact">Send an enquiry</a>
    </div>
    <!-- STAAH booking engine: paste the property's booking widget embed here to take
         reservations directly on this page. It replaces nothing above; the WhatsApp,
         phone and enquiry routes stay as fallbacks. -->
  </div>
</section>

</main>

<div class="lightbox" role="dialog" aria-modal="true" aria-label="{p["name"]} gallery">
  <button class="lightbox-close" type="button" aria-label="Close gallery">&times;</button>
  <button class="lightbox-nav lightbox-prev" type="button" aria-label="Previous image">&lsaquo;</button>
  <img src="" alt="">
  <button class="lightbox-nav lightbox-next" type="button" aria-label="Next image">&rsaquo;</button>
</div>

<footer class="site-footer">
  <div class="container footer-top">
    <div class="footer-brand">
      {brand(p, footer=True)}
      <p>{p["name"]} is part of Gateway Lodge Group, a growing collection of hospitality
        destinations across Ghana.</p>
      <div class="social-icons">{socials}</div>
    </div>
    <div>
      <h3>This property</h3>
      <ul>
        <li><a href="#about">About</a></li>
        <li><a href="#facilities">Facilities</a></li>
        <li><a href="#gallery">Gallery</a></li>
        <li><a href="#contact">Location &amp; contact</a></li>
        <li><a href="#book">Book now</a></li>
      </ul>
    </div>
    <div>
      <h3>Gateway Lodge Group</h3>
      <ul>
        <li><a href="{GROUP}/">Group website</a></li>
        <li><a href="{GROUP}/properties.html">Our properties</a></li>
        <li><a href="{GROUP}/offers.html">Offers</a></li>
        <li><a href="{GROUP}/about.html">About us</a></li>
        <li><a href="{GROUP}/contact.html">Contact</a></li>
      </ul>
    </div>
  </div>
  <div class="container footer-bottom">
    <p>&copy; 2026 Gateway Lodge Group. All Rights Reserved.</p>
    <ul>
      <li><a href="{GROUP}/privacy-policy.html">Privacy Policy</a></li>
      <li><a href="{GROUP}/terms.html">Terms &amp; Conditions</a></li>
    </ul>
  </div>
</footer>

<div class="mobile-book"><a class="btn btn-gold" href="#book">Book Now</a></div>

<script>
{JS}</script>
</body>
</html>
"""

    with open(os.path.join(out, "index.html"), "w", encoding="utf-8") as f:
        f.write(html)
    with open(os.path.join(out, "landing.css"), "w", encoding="utf-8") as f:
        f.write(CSS)
    with open(os.path.join(out, "robots.txt"), "w", encoding="utf-8") as f:
        f.write(f"User-agent: *\nAllow: /\n\nSitemap: {url}sitemap.xml\n")
    with open(os.path.join(out, "sitemap.xml"), "w", encoding="utf-8") as f:
        f.write(
            '<?xml version="1.0" encoding="UTF-8"?>\n'
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
            f"  <url><loc>{url}</loc><changefreq>monthly</changefreq>"
            "<priority>1.0</priority></url>\n</urlset>\n"
        )
    print(f'built {p["slug"]}/  ->  {p["domain"]}')


if __name__ == "__main__":
    for prop in PROPERTIES:
        build(prop)
