## 📦 prop-access
![CI](https://github.com/nandan108/prop-access/actions/workflows/ci.yml/badge.svg)
![Coverage](https://codecov.io/gh/nandan108/prop-access/branch/main/graph/badge.svg)
![Style](https://img.shields.io/badge/style-php--cs--fixer-brightgreen)
![Packagist](https://img.shields.io/packagist/v/nandan108/prop-access)

**A minimal and extensible property accessor library for PHP objects.**

Provides getter and setter resolution via reflection, supporting both public properties and `get*/set*` methods.

Designed to be:

* ✅ Framework-agnostic
* 🔌 Easily extensible

---

### 🛠 Installation

```bash
composer require nandan108/prop-access
```

---

### 🔧 Features

* 🧠 Default resolvers for public properties and `getProp()`/`setProp()` methods
* 🧩 Pluggable resolver priority (later-registered resolvers are called first)
* 🧼 `CaseConverter` utility for camelCase, snake\_case, kebab-case, etc.

---

### 🚀 Usage

Accessor maps are **cached by class name**, so the returned closures are **stateless** and require the target object to be passed as an argument:

```php
use Nandan108\PropAccess\AccessorRegistry;

$getterMap = AccessorRegistry::getGetterMap($myObj);
$value = $getterMap['propertyName']($myObj);

$setterMap = AccessorRegistry::getSetterMap($myObj);
$setterMap['propertyName']($myObj, $newValue);
```

To resolve only specific properties:

```php
$getters = AccessorRegistry::getGetterMap($myObj, ['foo_bar']);
```

---

#### 🧐 Resolution Behavior

You can call `getGetterMap()` / `getSetterMap()` in two ways:

1. **Without property list**:
   Returns a full canonical map using camelCase keys. If both a public property (e.g. `my_prop`) and a corresponding getter (`getMyProp()`) exist, **only the getter will be included** to avoid duplication and ensure value transformation logic is preserved.

   ```php
   $map = AccessorRegistry::getGetterMap($entity);
   $map['myProp']($entity); // uses getMyProp(), not $entity->my_prop
   ```

2. **With a property list**:
   Allows access to both public properties and getter/setter methods via multiple aliases:

   * `foo_bar` → accesses the public property (if available)
   * `fooBar` → accesses the getter/setter method (if available)

   ```php
   [$directSetter, $indirectSetter] = AccessorRegistry::getSetterMap($myObj, ['foo_bar', 'fooBar']);

   $directSetter($myObj, 'A');   // -> $myObj->foo_bar = 'A';
   $indirectSetter($myObj, 'B'); // -> $myObj->setFooBar('B');
   ```

---

### 🔌 Custom Accessor Resolvers

Resolvers can be registered to override or extend behavior:

```php
AccessorRegistry::bootDefaultResolvers(); // Registers built-in property/method resolvers

AccessorRegistry::registerGetterResolver(new MyCustomGetterResolver());
AccessorRegistry::registerSetterResolver(new MyCustomSetterResolver());
```

Later-registered resolvers are tried first. If `->supports($object)` returns false, fallback continues down the chain.

---

### 🧬 CaseConverter Utility

```php
CaseConverter::toCamel('user_name');     // "userName"
CaseConverter::toPascal('user_name');    // "UserName"
CaseConverter::toSnake('UserName');      // "user_name"
CaseConverter::toKebab('UserName');      // "user-name"
CaseConverter::toUpperSnake('UserName'); // "USER_NAME"
```

You can also use the generic method:

```php
CaseConverter::to('camel', 'foo_bar'); // Equivalent to toCamel()
```

---

### ✅ Quality

* ✅ 100% test coverage
* ✅ Psalm clean
* ✅ Zero dependencies

---

MIT License © [nandan108](https://github.com/nandan108)

