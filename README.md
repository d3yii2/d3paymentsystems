#D3 Payment Systems"

## Features
### models and components for d3pPersonContacts
 - Skrill
 - Luxon
 - Cripto

## Installation
Too app composer.json require add

```json
"d3yii2/d3paymentsystems": "dev-master"
```

Translation
```php
    'd3paymentsystems => [
        'class' => \'yii\i18n\PhpMessageSource\',
        'basePath' => '@vendor/d3yii2/d3paymentsystems/messages',
        'sourceLanguage' => 'en-US',
    ],
```

## config
### skrill component
Config
```php 
        'PersonSettingSkrill' => [
            'class' => 'd3yii2\d3paymentsystems\components\PersonSettingSkrill',
            'contactTypeId' => 4,
            'currencyList' => ['EUR','USD','MULTI']
        ],
```

Migration

```php 
        $model = new D3pContactType();
        $model->id = D3pContactType::TYPE_SKRILL;
        $model->class_name = 'component:PersonSettingSkrill';
        $model->setGroupSettings();
        if (!$model->save()) {
            throw new d3system\exceptions\D3ActiveRecordException($model);
        }
        $model->language = 'ru';
        $model->name = 'Skrill';
        if (!$model->saveTranslation()) {
            throw new d3system\exceptions\D3ActiveRecordException($model);
        }
```


### Crypto component
```php 
        'PersonSettingCrypto' => [
            'class' => 'd3yii2\d3paymentsystems\components\PersonSettingCrypto',
            'contactTypeId' => 17,
            'typeDef' => [
                'BNB' => [
                    'bep20'
                ]
            ]
        ],
```

migration
```php 
        $model = new D3pContactType();
        $model->id = D3pContactType::TYPE_CRYPTO;
        $model->class_name = 'component:PersonSettingCrypto';
        $model->setGroupSettings();
        if (!$model->save()) {
            throw new d3system\exceptions\D3ActiveRecordException($model);
        }
        $model->language = 'ru';
        $model->name = 'Crypto';
        if (!$model->saveTranslation()) {
            throw new d3system\exceptions\D3ActiveRecordException($model);
        }
```

### Luxor component
```php 
        'PersonSettingLuxon' => [
            'class' => 'd3yii2\d3paymentsystems\components\PersonSettingLuxon',
            'contactTypeId' => 16,
        ],
```
migration
```php 
        $model = new D3pContactType();
        $model->id = D3pContactType::TYPE_LUXON;
        $model->class_name = 'component:PersonSettingLuxon';
        $model->setGroupSettings();
        if (!$model->save()) {
            throw new d3system\exceptions\D3ActiveRecordException($model);
        }
        $model->language = 'ru';
        $model->name = 'Luxon';
        if (!$model->saveTranslation()) {
            throw new d3system\exceptions\D3ActiveRecordException($model);
        }
```


## Display value
```php 
    $component = Yii::$app->$componentName;
    $component->findModel($model->id);
    $options = []; // for each component can be own options
    echo $component->showValue($options);
```

## Examples
