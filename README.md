Ting DBC PHP5 Client
====================
A library[*](#star_mark) for accessing the [Ting][] API developed by [DBC][]
using PHP5.

Requirements
------------
NanoSOAP
Drupal 7 (for testing)

The client currently supports:
------------------------------
* Searching the API
* Spelling and autocomplete suggestions

The client has been developed for use with [Drupal][], but we have
attempted to make it as independent of Drupal as possible. Over time there has
been some changes which no longer makes it independent of Drupal when testing
the library. Some parts of the library is spefically targeting the logging
system for Drupal.

It should be possible to use the Ting Client as a library, but it has not been
tested.

Running tests
-------------
The tests are run through the SimpleTest framework included with Drupal 7
and later versions. Because of shutdown of OpenSearch 1.x and refactoring of
the library the tests need some work.

Historical
----------
Ting Client was originally design for the data well OpenSearch 1.x which has
been extended and replaced by OpenSearch 2.x. The former version was taken out
of service on April 27 2012 and is not available any longer.


[Ting]: http://ting.dk/
[DBC]: http://dbc.dk/
[Drupal]: http://drupal.org/
--------------
*Footnotes:*

<a name="star_mark"> * </a>The library is no longer a library but a module due to the use of Drupal's test framework.

Mockups
-------
Mockups are static dumps of objects found in the tests/mockups folder. Primarialy used as objects in unittests.

### processResponse_response_dump_full_view_search:
JSON encoded dump of the response object dumped from the first line in the processResponse method in the TingClientObjectCollection object.
Based on a full_view search.

### processResponse_response_dump_short_view_search:
JSON encoded dump of the response object dumped from the first line in the processResponse method in the TingClientObjectCollection object.
Based on a short_view search.
