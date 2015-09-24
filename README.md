# country-geotime
*country-geotime* [dataset](https://github.com/datasets), for geospatial information related to countries.

## Introduction
The iniciative of this repo comes from a discussion at [country-codes/issues/22](https://github.com/datasets/country-codes/issues/22) and [datasets/registry/issues/119](https://github.com/datasets/registry/issues/119#issuecomment-142620724).
...


## Preparation
There are many "good way" to prepare and join CSV files,  using UNIX basic tools, awk, or MongoDB. When complex "CSV+JSON data" is nencessary, MongoDB is the best option. The following below shows the preparation of each column, them a mongo command join all. Some data was prepared from maps and checked by Wikidata, others are "ready for use" official data,  transformed in worksheet.

### ID coluns
The election of affordable contry IDs fell in choosing the most popular *ISO 3166-1* labels, the *alpha-2*  for humans and `ISO3166-1-numeric` for machines. Any other can be obtained by join with [country-codes](https://github.com/datasets/country-codes).

### Country neighbors
Natural Earth have free vector  map data at 1:10m, with contry polygions and contry-codes: [ne_10m_admin_0_countries.zip](http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_0_countries.zip). After download and analysis we discover that is not so good about topology, and we can't to use `ST_Touches()` as planed (for a rigorous derivation from the semantic definition)... An equivalent but no so reliable technic to extract neighbors information is the  `ST_dwithin()` function. Testing the minimal and maximal reliable parameter (100 to 1000), we found that the map have reliable "interpreted topology".


```sql
  SELECT ref_country, array_agg(neighbor) AS neighbor_list
  FROM (
    SELECT DISTINCT
        m.iso_a2 AS ref_country, scan.iso_a2 AS neighbor
    FROM mundi AS m INNER JOIN mundi AS scan
         ON ST_dwithin(m.geog, scan.geog, 100)
    WHERE m.gid > scan.gid
    ORDER BY 1, 2
  ) source
  GROUP BY ref_country
  ```
This query is not fast, need somo hours to run. See also [preparation analysis  report](https://github.com/ppKrauss/country-geotime/wiki/Country-neighbors-preparation).

### ... UTM ...


### ... time ...
...

### Other suggestions
... language ...  Wikidata/Wikipedia conventions for "simple and useful data"... 




