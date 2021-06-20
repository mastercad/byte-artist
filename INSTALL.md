composer create-project symfony/skeleton byte-artist
composer require twig doctrine
composer require console
composer require security
composer require logger
composer require annotations
composer require symfony/maker-bundle --dev
composer require symfony/webpack-encore-bundle
composer require friendsofsymfony/ckeditor-bundle

yarn install
yarn add @symfony/webpack-encore --dev
yarn add bootstrap jquery popper.js --dev
yarn add --dev @fortawesome/fontawesome-free
yarn add copy-webpack-plugin

# neu - war aber glaub doch überflüssig. 
yarn add @symfony/webpack-encore sass-loader node-sass lodash.throttle -D
yarn add bootstrap bootstrap.native glightbox axios form-serialize @fortawesome/fontawesome-svg-core @fortawesome/free-brands-svg-icons @fortawesome/free-regular-svg-icons @fortawesome/free-solid-svg-icons


// yarn add bootstrap-theme --dev -> wird nicht mehr supported
bin/console make:auth
php bin/console doctrine:database:create
php bin/console make:migration => legt eine migration für user table an
php bin/console doctrine:migrations:migrate => user table anlegen
composer require validator => wird für registration benötigt
composer require form => wird ebenfalls von mindestens registration benötigt
php bin/console make:registration-form => legt die registrierungs action an
php bin/console doctrine:schema:validate => entity vs datenbank validieren
php bin/console doctrine:migrations:diff => legt eine migration aus einem diff zwischen entity und datenbank an

php bin/console doctrine:mapping:import "App\Entity" annotation --path=src/Entity
php bin/console make:entity --regenerate App
php bin/console make:form => legt form an hand des entitiy an
php bin/console generate:doctrine:form App\Entity\Blog => legt form für Blog an (gibts nur mit SensioGeneratorBundle)

php -r 'echo base64_encode(require "config/secrets/prod/prod.decrypt.private.php");'

########################################################################################################################
########################################################################################################################
########################################################################################################################

Fortgeschritten:
integrieren von OAuth für google und facebook:
composer require knpuniversity/oauth2-client-bundle
composer require league/oauth2-google
composer require league/oauth2-facebook

replace absolute paths in coverage-clover.xml
sed -i 's/\(.*name="\).*byte_artist\/\(.*"\)/\1\2/g' build/reports/coverage-clover.xml

########################################################################################################################
######################           fixtures ##############################################################################
########################################################################################################################
