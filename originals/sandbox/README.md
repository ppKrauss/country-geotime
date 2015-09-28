# country-geotime, preparation reports

## ID coluns
The election of affordable contry IDs fell in choosing the most popular *ISO 3166-1* labels, the `alpha-2`  for humans and `numeric` for machines. Any other can be obtained by join with [country-codes](https://github.com/datasets/country-codes).

... Evidências de uso mais frequente:
* alpha2 está nos dominios da internt, na Wikipedia, etc. nos padrões-usuários como lang
* numeric está nas bases de dados, 

## Country neighbors
* Source data
* Conformation: Wikidata 
* carga into SQL
* SQL script
* exporting as CSV
* joing data 

https://github.com/ppKrauss/country-geotime/wiki/Country-neighbors,-preparation-report



## ... UTM and Time
Mostrar como se transporma com PostGIS 


## Other suggestions
... language ...  Wikidata/Wikipedia conventions for "simple and useful data"... 



## Human check and review
Some checking and reviews was made by human inspecion, before to usa a full-automated process. The git and version control helps the editings, with use of git/issue feature for bug-tracking.






=====


//  Falta LEFT JOIN de ctneig com duas colunas 
//lixoo db.ctcodes.find({},{"ISO3166-1-Alpha-2":1,"ISO3166-1-numeric":1})

// query de join

//? db.lixo.dropColection()

//	db.createCollection(name, { size : ..., capped : ..., max : ... } )

db.ctimes.find().count()
db.ctcodes.find().sort({"ISO3166-1-Alpha-2":1}).forEach(function(doc) {
		var alpha = doc["ISO3166-1-Alpha-2"];
		if (doc["name"])
		db.ctneig.update({_id:doc._id}, {$set: {neighbor_list: tmp}});
});


//França com erro, não virou ilha!  deve ser por questão de multi-territorio.



var x=[];
db.ctcodes.find().sort({"ISO3166-1-Alpha-2":1}).forEach(function(doc) {
		var alpha = doc["ISO3166-1-Alpha-2"];
		var num  =  doc["ISO3166-1-numeric"];
		var newdoc = {isoalpha2: alpha, isonum: num};
		db.lixo.save(newdoc)
});



criar labwork para simplificar!
