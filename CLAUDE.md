# Gateway Lodge Group

## The build files are the source of truth

Every change goes into a build file. Not into a page, not into the live site
by hand, not into a generated file. This is not a step to be asked for, and it
is not done afterwards if there is time: a change that exists only on a server
is a change that the next rebuild silently reverts.

```
tools/build-landings.py              PROPERTIES - all copy and imagery, for
                                     every property, for both builds
tools/build-wp-property-content.py   -> gwl-property-content.php   (generated)
tools/build-wp-footers.py            -> gwl-property-footers.php   (generated)
wordpress/novamira-sandbox/          the Elementor builder itself
```

So:

- **Copy or imagery** changes in `PROPERTIES`, then the generators are re-run.
  Never in the two generated PHP files, whose headers say the same thing.
- **Layout or widget behaviour** changes in the builder PHP, then that file is
  fetched onto the server and `gwl_nrp_build( '<slug>' )` is re-run. Never by
  editing a page in Elementor.
- **Media** goes in `<slug>/media/` and is uploaded by `gwl_nrp_sideload()`,
  which reads the manifest the generator emits. Never uploaded by hand.
- **Anything learned the hard way** goes in
  `wordpress/PROPERTY-SUBDOMAIN-RUNBOOK.md`, and where it can be checked
  mechanically, into a check (see below).

The three properties share one builder, so a fix written properly reaches all
three on their next rebuild. A fix applied to one live site reaches nothing.

## Before saying a change is done

```bash
python3 tools/build-landings.py      # rebuilds, then warns
python3 tools/build-wp-property-content.py
python3 tools/build-wp-footers.py
python3 tools/check-wp-builder.py    # exits non-zero on a real fault
```

`check-wp-builder.py` fails if a generated file is out of date with
`PROPERTIES`, which is what catches a change that was made in the wrong place.

## Verifying

The sites are unreachable from this environment, and server-side checks only
confirm that settings exist - they cannot show how those settings compose. A
doubled-width header, an empty gallery and a hero video that never played all
passed every server-side check. Mirror the page and render it; the method is in
section 6 of the runbook.

## House rules for the pages

- Native Elementor containers only. No sections, no inner sections, no atomic
  elements, no HTML widgets, no Elementor Pro widgets.
- Header and footer are XPRO Theme Builder templates, built from XPRO widgets.
- Forms are WPForms, placed through Elementor's shortcode widget.
- No em dash in customer-facing copy.
- Describe a photograph by what is in the frame. Six filenames across the three
  shoots do not match their contents.
