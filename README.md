# linker
A Drupal 8 input filter to convert [Title string] into links.

## Purpose

To create wiki-style inputs for links, so that editors do not
need to lookup additional content while writing.

## Roadmap

* Create an input filter to support [string] replacement, where
"string" is a node title or taxonomy term.
* Create a plugin system to register additional types of entities
to be used with the filter.
* Create a means to generate placeholder content if the string
fails to match any targets.
