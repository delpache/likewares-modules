# Gestion de modules pour Laravel

Ce package a pour but de rendre votre application Laravel extensible via des modules. Un module est une sorte de petite application Laravel, livrée avec ses propres vues, contrôleurs, modèles, etc.

## Démarrer

### 1. Installation

Exécutez la commande suivante :

```bash
composer require likewares/likewares-modules
```

### 2. Enregistrement

Le Service provider et la façade seront enregistrés automatiquement. Si vous voulez les enregistrer manuellement dans `config/app.php` :

```php
Likewares\Module\Facade::class,
Likewares\Module\Providers\Laravel::class,
```

### 3. Publication

Publication du fichier de configuration.

```bash
php artisan vendor:publish --tag=module
```

### 4. Configuration

Vous pouvez modifier la configuration dans le fichier `config/module.php`.

### 5. Autoloading

Par défaut, les classes des modules ne sont pas chargées automatiquement. Vous pouvez charger automatiquement vos modules en utilisant `psr-4`. Par exemple:

``` json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "modules/"
    }
  }
}
```

**Astuces : n'oubliez pas de lancer `composer dump-autoload` par la suite.**

## License

La licence MIT (MIT). Veuillez consulter [LICENSE](LICENSE.md) pour plus d'informations.
