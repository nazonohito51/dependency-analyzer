# DependencyAnalyzer

Analyze/Verify dependency map for php.

[![Latest Stable Version](https://poser.pugx.org/nazonohito51/dependency-analyzer/version)](https://packagist.org/packages/nazonohito51/dependency-analyzer)
[![Build Status](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nazonohito51/dependency-analyzer/?branch=master)

## Description

Dependency analyzer help you to keeping clean your architecture.

If you start to managing dependencies between classes likely [Clean Architecture]() or [Layered Architecture](), you will aware inspecting dependency between classes by eye is very difficult in PHP.
This library analyze dependencies in your repository, and take some way of using it to you.(Example: Create graph, Verify rule, Detect cycle path)

## Basic Usages
### Create dependency graph

![graph](./dependency_graph_sample.png)

```bash
php ./bin/analyze-deps graph ./some/analyze/dir
```

Analysis dependency map and create graph. Now, dependency analyzer support only [Plant UML]() format.

### Verify your dependency rule
You can define your dependency rule by two ways. And, this library will detect rule violation in your repository, and notify them to you.

First way is defining by php file. In under sample, 

```php
<?php
$controllerDefine = ['\App', '!\App\Providers'];
$applicationDefine = ['\Acme\Application'];
$domainDefine = ['\Acme\Domain'];
$repositoryDefine = ['\Acme\Domain\Repositories'];

return [
    'layer dependency rule' => [                // name of your rule
        'ControllerLayer' => [                  // component name
            'define' => $controllerDefine,      // component definition by namespace
        ],
        'ApplicationLayer' => [
            'define' => $applicationDefine,
            'depender' => $controllerDefine,    // rule of component dependency
        ],
        'DomainLayer' => [
            'define' => $domainDefine,
            'depender' => $applicationDefine
        ]
    ]
];
```

```bash
php ./bin/analyze-deps verify --rule ./conf/rule_sample.php ./some/analyze/dir
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
Dependency is knowledge of interface is had by every class. 
In classes colabolation, every class will have knowledge of other classes.
If some interfaces is changed, classes know that interfaces is must fixed for change of interface.

Dependency is created by under php syntaxs.

* Type hinting
* Return value type
* call public method
* fetch public property/constant
* extends/implements
* throw
* catch

This library analyze those syntaxs by using [PHPStan](), and crate dependency map.

## Advanced Usages
### Create dependency graph
wiki

* rule file
* namespace
* group
* comment

### Verify your dependency rule
wiki

* rule file
  * multiple rules
  * depender/dependee
* phpdoc
* namespace rule
* magic keyword

### Detect cycle dependency
wiki

## TODO
- [ ] README
  - [ ] graph
  - [ ] wiki
- [x] Response object & format
  - [x] use table format
- [ ] comment of Plant UML
- [ ] fix namespace pattern matting(adjust file pattern matting)
  - [ ] \Hoge\Fuga\*
  - [ ] only !\Hoge\Fuga
- [ ] Graph format(another puml)
- [ ] original rule logic
  - [ ] remove dependency to vertex, edge
