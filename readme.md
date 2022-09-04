# FME App Backend Rest API 
Lumen Framework
#To use php artisan command with lumen
cd /to-your-lumen-project root, then enter the below:
- composer require flipbox/lumen-generator

Then register this package in your bootstrap/app.php file as under Register Container Binding

$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
