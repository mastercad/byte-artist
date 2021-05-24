#########################################################################################################################
# SQLSTATE[HY000] [2002] Connection refused
Hier war das problem, dass die config mit localhost angelegt wurde beim installieren der pakete, 
die DB aber im docker env ne eigene IP hat ... didum ...

#########################################################################################################################
# DB Connection brachte:
An exception occurred in driver: SQLSTATE[HY000] [2054] The server requested authentication method unknown to the client

=> ALTER USER 'username'@'localhost' IDENTIFIED WITH mysql_native_password BY 'PASSWORD';

#########################################################################################################################
Problem:
bin/console make:auth => 
                                                     
  The "authenticator-type" argument does not exist.  
                                                     

make:auth [-h|--help] [-q|--quiet] [-v|vv|vvv|--verbose] [-V|--version] [--ansi] [--no-ansi] [-n|--no-interaction] [-e|--env ENV] [--no-debug] [--] <command>

Lösung: war nach neu starten des containers weg

#########################################################################################################################
Problem:
bin/console make:user => 
2019-07-29T20:15:29+01:00 [critical] Argument 2 passed to Symfony\Bundle\MakerBundle\Security\UserClassConfiguration::__construct() must be of the type string, null given, called in /project/vendor/symfony/maker-bundle/src/Maker/MakeUser.php on line 124

In UserClassConfiguration.php line 31:
                                                                               
  Argument 2 passed to Symfony\Bundle\MakerBundle\Security\UserClassConfigura  
  tion::__construct() must be of the type string, null given, called in /proj  
  ect/vendor/symfony/maker-bundle/src/Maker/MakeUser.php on line 124           
                                                                               

make:user [--is-entity] [--identity-property-name IDENTITY-PROPERTY-NAME] [--with-password] [--use-argon2] [-h|--help] [-q|--quiet] [-v|vv|vvv|--verbose] [-V|--version] [--ansi] [--no-ansi] [-n|--no-interaction] [-e|--env ENV] [--no-debug] [--] <command> [<name>]

Lösung: war nach neu starten des containers weg

#########################################################################################################################
Problem:
root@e8e9d1f42ced:/application# php bin/console doctrine:database:create

In DriverManager.php line 269:
                              
  Malformed parameter "url".
                              

Lösung:
1. Schritt:
	aus doctrine.yaml resolve:DATABASE_URL entfernt => ist nur notwendig, wenn in dem URL noch irgendwas aufgelöst werden muss => ändert nichts
2. Schritt:
	das passwort urlencodet mit https://www.urlencoder.io/ # php -r "echo urlencode('yShe74#8');" bringt das selbe ergebnis => ändert nichts
3. hat alles nichts geholfen. im grunde ar alles nach einem neu start weg

#########################################################################################################################

 php bin/console doctrine:schema:validate

Mapping
-------

 [FAIL] The entity-class App\Entity\Blog mapping is invalid:
 * The mappings App\Entity\Blog#tags and App\Entity\BlogTags#blog are inconsistent with each other.


 [FAIL] The entity-class App\Entity\BlogTags mapping is invalid:
 * The association App\Entity\BlogTags#blog refers to the inverse side field App\Entity\Blog#blog_tags which does not exist.


 [FAIL] The entity-class App\Entity\Projects mapping is invalid:
 * The mappings App\Entity\Projects#tags and App\Entity\ProjectTags#projects are inconsistent with each other.


 [FAIL] The entity-class App\Entity\ProjectTags mapping is invalid:
 * The association App\Entity\ProjectTags#projects refers to the inverse side field App\Entity\Projects#project_tags which does not exist.


Database
--------

                                                                                                                        
 [ERROR] The database schema is not in sync with the current mapping file.                                              
                                                                            
- zu erst die änderungen zwischen entity und datenbank anzeigen:
php bin/console doctrine:schema:update --dump-sql
- diese änderungen als migration anlegen:
php bin/console doctrine:migrations:diff
- die migration einspielen
php bin/console doctrine:migrations:execute --up 20190816154506
- migration bzw. sync direkt durchführen:
php bin/console doctrine:schema:update --force --complete --dump-sql

#########################################################################################################################
es kommt immer wieder:
The following SQL statements will be executed:

     ALTER TABLE migration_versions CHANGE version version VARCHAR(14) NOT NULL;

