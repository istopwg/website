#!/bin/sh
clang -o regtostrings -Os -g regtostrings.c -lmxml
./regtostrings ipp-registrations.xml | sort -u >ipp.strings
