# country-geotime
*country-geotime* [dataset](https://github.com/datasets), for geospatial information related to countries.

## Introduction
The iniciative of this repo comes from a discussion at [country-codes/issues/22](https://github.com/datasets/country-codes/issues/22) and [datasets/registry/issues/119](https://github.com/datasets/registry/issues/119#issuecomment-142620724).
...


## Preparation
There are many "good way" to prepare and join CSV files,  using UNIX basic tools, awk, or MongoDB. When complex "CSV+JSON data" is nencessary, MongoDB is the best option. The following below shows the preparation of each column, them a mongo command join all. Some data was prepared from maps and checked by Wikidata, others are "ready for use" official data,  transformed in worksheet.

### ID coluns
The election of affordable contry IDs fell in choosing the most popular *ISO 3166-1* labels, the `alpha-2`  for humans and `numeric` for machines. Any other can be obtained by join with [country-codes](https://github.com/datasets/country-codes).

### Country neighbors
source and process (using PostGIS) ... See more [details at report](https://github.com/ppKrauss/country-geotime/wiki/Country-neighbors,-preparation-report)

### ... UTM ...


### ... time ...
...

### Other suggestions
... language ...  Wikidata/Wikipedia conventions for "simple and useful data"... 




