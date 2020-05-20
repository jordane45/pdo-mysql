# pdo-mysql
class d'utilisation d'une bdd mysql en PDO

#Pour l'utiliser, faites juste un require de la class
<code>
require_once './class/db.class.php';
</code>
puis instanciez la class
<code>
$odb = new db(); // par défaut, utilisera la config "dev"

$odb = new db("prod"); // en spéciant l'environnement, la connexion utilisera la config "prod"
</code>

#Configurer la connexion à la bdd
La configuration se fait dans le fichier /config/bdd.json

<code>
{
  "prod": {
    "dbname": "mabdd",
    "host": "localhost",
    "port": 3306,
    "username": "leUserDeProd",
    "password": "lePassword",
    "charset": "utf8"
  },
  "dev": {
    "dbname": "mabdd_dev",
    "host": "localhost",
    "port": 3306,
    "username": "leUserDeDev",
    "password": "lePassword",
    "charset": "utf8"
  }

}
</code>
