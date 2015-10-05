# country-geotime
*country-geotime* [dataset](https://github.com/datasets), for geospatial information related to countries.

## Introduction
The motivations of this repo comes from a discussion at [country-codes/issues/22](https://github.com/datasets/country-codes/issues/22) and [datasets/registry/issues/119](https://github.com/datasets/registry/issues/119#issuecomment-142620724).
...

## Data 
There are two CSV tables, both described by [`datapackage.json`](datapackage.json),

* [`contry-geotime.csv`](data/contry-geotime.csv): the main data, ordered by iso_alpha2 and listing neighbors, official languages and legal time.

* [`territoryContainment.csv`](data/territoryContainment.csv): a "type,contains" table describing standard geographical agregators as European Union (EU) or Soth America (005).

## Preparation

Some data was prepared from maps and checked by Wikidata, others, as `unicode_CLDR_tr35` folder, are "ready for use" official data, where the only work was to transform into worksheet.

The spatial data come from different reliable open sources, and can be prepared by standard SQL (like PostgreSQL) and OGC-compliant tool (ex. PostGIS).  The *preparation scripts*, are relevant part of this dataset, in the roleof its "recipe". Sources are described also by the `datapackage.js` metadata file.

### ID coluns
This dataset is a complement of [country-codes](https://github.com/datasets/country-codes), that use *ISO 3166-1* labels. 
The most popular label is the `alpha-2`  (for humans), and, for other uses,  `numeric`. Any other ID (ex. FIFA or Marc) can be obtained by join with `country-codes` tables.

### Country neighbors
source and process (using PostGIS) ... See more [details at report](https://github.com/ppKrauss/country-geotime/wiki/Country-neighbors,-preparation-report).

For contain.. territoryContainment of CLDR-tr35

###  UTM references
Under construction. Each country is under one or more cells of the UTM-grid, that is the main standard to describe contry territory in a local-planar projection.

### Time references 
Each country have its standard official UTC hour fuses (column FUSES), corresponding to an official approximation to the exact UTC fuses tha cross the country area; and have an extetion to these fuses, the "legal time" (column `DST_legal`) corresponding to the....

unicode_CLDR_tr35

### Language 

Unicode, Inc. CLDR data files are interpreted according to the LDML specification [unicode.org/reports/tr35](http://unicode.org/reports/tr35/). All CLDR-tr35 data is expressed in the [`supplementalData.xml`](originals/supplementalData.xml) and was extracted (convertion to CSV) by [extract.php](originals/sandbox/unicode_CLDR_tr35/extract.php),

* list of official and secondary languages per country: 
* percentage of population that use a secondary language: this dataset was prepared with a 1% treshold.



### Other suggestions

... language ...  Wikidata/Wikipedia conventions for "simple and useful data"... 

## Credits

Thanks to [@sumariva](https://github.com/sumariva) in prepare and review all PostGIS scripts, and generate results after hours of CPU. 

