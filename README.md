# country-geotime
*country-geotime* [dataset](https://github.com/datasets), for geospatial information related to countries.

## Introduction
The motivations of this repo comes from a discussion at [country-codes/issues/22](https://github.com/datasets/country-codes/issues/22) and [datasets/registry/issues/119](https://github.com/datasets/registry/issues/119#issuecomment-142620724).

The  preparation use more than one source, and many methods to extract data. 

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

As showed in [peakbagger's depentab](http://peakbagger.com/pbgeog/countries.aspx#depentab), *"(...) there are therefore 254 'countries' in the world: 194 independent nation-states, 55 dependencies, Antarctica, and 4 other areas"*, so a map of units for the 194, and a "full map" for join all other contries. Here, using  [Natural Earth  vector maps](http://www.naturalearthdata.com/downloads/10m-cultural-vectors/10m-admin-0-countries/) of each methodology.

```sql
  CREATE TABLE neighbors AS
  WITH mundi_aug AS (
    SELECT iso_a2, geom FROM countries WHERE iso_a2 NOT IN (SELECT iso_a2 FROM mundi)
    UNION 
    SELECT iso_a2, geom FROM mundi WHERE iso_a2!='-99' -- no ocean
  ) SELECT ref_country, array_agg(neighbor) AS neighbor_list
    FROM (
	    SELECT DISTINCT m.iso_a2 AS ref_country, scan.iso_a2 AS neighbor
	    FROM mundi_aug AS m INNER JOIN mundi_aug AS scan
		 ON ST_dwithin(m.geom, scan.geom, 0.00001) -- SRID4326 degree metric
	    WHERE m.iso_a2!=scan.iso_a2 -- not optimized, scans geom twice
	    ORDER BY 1, 2
    ) source
    GROUP BY ref_country;
```
The *distance_of_srid* parameter of [ST_DWithin](http://postgis.net/docs/ST_DWithin.html) can range from 0.0000001 to 0.0001, so the country's neighbor topology is stable, and into a 100 or less meters error scale.

```shell
# # # # # # #
# get main mundi map, witn only ~190 "country units"
mkdir sandbox
cd sandbox
wget -c http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_0_map_units.zip
unzip ne_10m_admin_0_map_units.zip
shp2pgsql  -s 4326 ne_10m_admin_0_map_units public.mundi | psql -h localhost -U postgres sandbox
rm *.*
# get secondary mundi map, witn all ~240 countries
wget -c http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_0_countries.zip
unzip ne_10m_admin_0_countries.zip
shp2pgsql  -s 4326 ne_10m_admin_0_countries public.countries | psql -h localhost -U postgres sandbox
rm *.*

psql -h localhost -U postgres sandbox -c < createNeighbors.sql

# make a report as COPY CSV or 
psql -h localhost -U postgres sandbox -c "select *, array_length(neighbor_list,1) list_len from neighbors"
```

See more [details at report](https://github.com/ppKrauss/country-geotime/wiki/Country-neighbors,-preparation-report).

About territoryContainment of CLDR-tr35 ...

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


