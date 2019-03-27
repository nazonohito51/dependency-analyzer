# DependencyAnalyzer

Analyze/Verify dependency map for php.

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
php ./bin/analyze-deps detect-cycle ./some/analyze/dir --rule ./conf/rule_sample.php
```

```bash
array(1) {
  [0] =>
  string(131) "App\UseCaseRequests\GetUserRequest(ControllerLayer) must not depend on Acme\Domain\Entities\User(DomainLayer)."
}
```

### Detect cycle dependency

```bash
php ./bin/analyze-deps detect-cycle ./some/analyze/dir
```

```bash
  array(6) {
    [0] =>
    string(39) "App\Http\Controllers\Api\UserController"
    [1] =>
    string(43) "Acme\Application\UseCases\GetUserInteractor"
    [2] =>
    string(42) "Acme\Application\Responses\GetUserResponse"
    [3] =>
    string(25) "Acme\Domain\Entities\User"
    [4] =>
    string(35) "App\Http\Controllers\UserController"
    [5] =>
    string(34) "App\UseCaseRequests\GetUserRequest"
  }
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
- [ ] Response object & format
- [ ] comment of Plant UML
- [ ] namespace pattern matting
- [ ] Graph format
- [ ] original rule logic
- [ ] remove dependency to vertex, edge
