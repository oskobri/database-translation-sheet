
# A package to translate your translated model with Google sheet

> :warning: **Package in development**
>
> :warning: **This package requires you use [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable) trait on your models.**

## Installation
```bash
 $ composer require oskobri/database-translation-sheet --dev  
```   
Configuration file need to be published to add your translated models.


```bash 
$ php artisan vendor:publish --provider="Oskobri\DatabaseTranslationSheet\DatabaseTranslationSheetServiceProvider" 
```   

TODO:
- Configuration (Google sheet / models)
- Usage
