#!/usr/bin/gawk -f
#
# A program to count the number of lines in a C source code file
# Written by David Reiss. Copyright (C) 2001 David Reiss.
# This file may be distrubuted under the conditions of the
# GNU GPL, version 2 or later.
#
# $Id: $

BEGIN {
    cb = "/\\*"                 # Comment begin
    ce = "\\*/"                 # Comment end
    ws = "[ \t]*"               # Whitespace
    ic = "([^*]|\\*+[^/])*"     # All characters in a comment
    fc = "(" cb ic ce "|//.*$)" # A full comment

    incom = 0        # Are we in a multi-line comment as the line starts?
    blanklines = 0   # Number of blank lines seen
    comlines = 0     # Number of lines containing comments and whitespace only
    bracelines = 0   # Lines containing only a single brace
    reallines = 0    # Real lines containing meaningful C code
}

$0 ~ "^" ws "$" {
    blanklines++
    next
}

# Special case: in a multi-line comment for entire line
incom && $0 !~ ce {
    comlines++
    next
}

incom {                  # Starts with multi-line comment, but ends
    sub(ic ce ws, "")    # Get rid of starting comment
    incom = 0
}

{
    # Clear the complete comments and whitespace
    gsub(fc, "")
    gsub(ws, "")
    if (match($0, cb)) {    # Does an unterminated comment start?
        incom = 1           # The line ends in the middle of a multi-line comment
        sub(cb ".*$", "")   # Get rid of it before processing
    }
}

/^$/ {
    # The line is now blank; it must have had only comments
    comlines++
    next
}

/^(\}|\{)$/ {
    # The line contains only a single brace; not a "real" line
    bracelines++
    next
}

{
    reallines++
}

END {
    # Print a report
    print "Blank lines  :\t", blanklines
    print "Comment lines:\t", comlines
    print "Brace lines  :\t", bracelines
    print "Real lines   :\t", reallines
    print "Total lines  :\t", blanklines + comlines + bracelines + reallines
}
