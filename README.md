# JsInsert - Variables js et css en php

Cette librairie permet de gerer simplement en php (sur Synfony) les declarations de variables javascript et css. 
On peux aussi lui fournir un script js qui s'executera au chargement de la page.


## Installation

Installer la dernière version via [Composer] en passant par le fichier composer.json

- Ajouter le repository [https://github.com/dev2choiz/jsinsert] et la dépendance [dev2choiz/jsinsert].
```composer.json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/dev2choiz/jsinsert"
        }
    ],
    "require": {
       "dev2choiz/jsinsert": "*"
    }
}
```
- Puis en ligne de commande, au niveau du fichier composer.json
```bash
$ composer update dev2choiz/jsinsert
```


## Usage

- Declaration des variables et script dans l'action du controleur.
Cette action doit contenir l'annotation [@JsInsert\Annotation\JsInsert] 

```php
<?php

    use JsInsert\JsInsert;

    /**
     * @\JsInsert\Annotation\JsInsert()
     */
    public function indexAction()
    {
        // ajout d'une variable javascript
        JsInsert::addVariable('string', 'myJsVar', 'foo');
        // ajout d'une variable css
        JsInsert::addCssVariable('my-css-var', '#9876AA');
        // ajout d'un script
        JsInsert::addScript('console.log("Hello !");');
        // ...
    }
```

- Utilisation de la variable js [myJsVar]
```javascript
alert(myJsVar);
```

- Utilisation de la variable css [my-css-var]
```css
    body {
        background-color: var(--my-css-var);
    }
```
- Quand à la console, elle affiche 
```console
Hello !
```
