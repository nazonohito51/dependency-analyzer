# DependencyAnalyzer

Analyze/Verify dependency map for php.

[![Latest Stable Version](https://poser.pugx.org/nazonohito51/dependency-analyzer/version)](https://packagist.org/packages/nazonohito51/dependency-analyzer)
[![Build Status](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/?branch=master)

## Description

Dependency analyzer help you to keeping clean your architecture.

If you start to managing dependencies between classes likely [Clean Architecture](http://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html) or Layered Architecture, you will aware inspecting dependency between classes by eye is very difficult in PHP.
This library analyze dependencies in your repository, and take some way of using it to you.(Example: Create UML graph, Verify by your rule, Detect cycle path)

## Basic Usages
### Create dependency graph

![graph](./dependency_graph_sample.png)

```bash
vendor/bin/analyze-deps graph ./you/want/to/analyze/dir --output ./graph.puml
```

Analysis dependency map and create graph. Now, dependency analyzer support only [Plant UML](https://github.com/plantuml/plantuml) format.

Maybe, your graph will have many classes, and is very complex! If you need to simplify your graph, see [Advanced Usage](#Advanced Usages). 

### Verify your dependency rule
In Clean Architecture, there is dependency rules between classes.
You can define your dependency rule, and this library will detect rule violation in your repository and notify them to you.

First way is defining by php file. In under sample, 

```php
<?php
// ./your_rule_file.php

$controllerDefine = ['\App', '!\App\Providers'];
$applicationDefine = ['\Acme\Application'];
$domainDefine = ['\Acme\Domain'];
$repositoryDefine = ['\Acme\Domain\Repositories'];

return [
    'layer dependency rule' => [                // name of your dependency rule
        'DomainLayer' => [                      // component name
            'define' => $domainDefine,          // component definition by namespace
            'depender' => $applicationDefine    // rule of component dependency, for depender
        ],
        'ApplicationLayer' => [
            'define' => $applicationDefine,
            'depender' => $controllerDefine,
        ],
        'ControllerLayer' => [
            'define' => $controllerDefine,
            'dependee' => []                    // rule of component dependency, for dependee
        ],
    ],
//    'some more rules' => [
//        'SomeComponent' => ['...'],
//        '...' => []
//    ]
];
```

`'component'` is a group of classes.
`'depender'` is classes that depend on component.
`'dependee'` is classes that is depended on component.
You can restrict depender/dependee.
Then, you can verify your repository like this:

```bash
php ./bin/analyze-deps verify --rule ./your_rule_file.php ./some/analyze/dir1  ./some/analyze/dir2
```

```bash
layer dependency rule
+------------------------------------+-----------------+----+---------------------------+-------------+
| depender                           | component       |    | dependee                  | component   |
+------------------------------------+-----------------+----+---------------------------+-------------+
| App\UseCaseRequests\GetUserRequest | ControllerLayer | -> | Acme\Domain\Entities\User | DomainLayer |
+------------------------------------+-----------------+----+---------------------------+-------------+
```

### Detect cycle dependency

```bash
php ./bin/analyze-deps detect-cycle ./some/analyze/dir
```

```bash
+---------------------------------------------+----+
| class                                       |    |
+---------------------------------------------+----+
| App\Http\Controllers\Api\UserController     | -> |
| Acme\Application\UseCases\GetUserInteractor | -> |
| Acme\Domain\Entities\User                   | -> |
| App\Http\Controllers\UserController         | -> |
| App\UseCaseRequests\GetUserRequest          | -> |
| App\Http\Controllers\Api\UserController     |    |
+---------------------------------------------+----+
```

## What is dependency?
Dependency is knowledge of interface that is had by class. 
In class collaboration, every class must have knowledge of interface of other classes.
If some interfaces is changed, classes what know interface is must fixed.

Dependency is created by under php syntaxes.

* Type hinting
* Return value type
* call public method
* fetch public property/constant
* extends/implements
* throw
* catch
* foreach
* array access
* others...

This library analyze those syntaxes by using [PHPStan](https://github.com/phpstan/phpstan), and crate dependency map.

## Advanced Usages
### Create dependency graph
TBD...

* rule file
* namespace
* group
* comment

### Verify your dependency rule
TBD...

* rule file
  * multiple rules
  * depender/dependee
* phpdoc
* namespace rule
* magic keyword

## TODO
- [ ] README
  - [ ] graph
  - [ ] wiki
- [ ] Analyze Facade
- [x] Response object & format
  - [x] use table format
- [ ] comment of Plant UML
- [ ] fix namespace pattern matting(adjust file pattern matting)
  - [ ] \Hoge\Fuga\*
  - [ ] only !\Hoge\Fuga
- [ ] Graph format(another puml)
- [ ] original rule logic
  - [ ] remove dependency to vertex, edge
