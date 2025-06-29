## 📦 prop-access
![CI](https://github.com/nandan108/prop-access/actions/workflows/ci.yml/badge.svg)
![Coverage](https://codecov.io/gh/nandan108/prop-access/branch/main/graph/badge.svg)
![Style](https://img.shields.io/badge/style-php--cs--fixer-brightgreen)
![Packagist](https://img.shields.io/packagist/v/nandan108/prop-access)

**A minimal and extensible property accessor library for PHP objects.**

Provides getter and setter resolution via reflection, supporting both public properties and `get*/set*` methods.

Designed to be:

* ✅ Framework-agnostic
* 🔌 Easily extensible to support more object types

---

### 🛠 Installation

```bash
composer require nandan108/prop-access
```

---

### 🔧 Features <a id="features"></a>

* 🧠 Default resolvers for public properties and `getProp()`/`setProp()` methods
* 🧩 Pluggable resolver priority (later-registered resolvers are called first)
* 🧼 `CaseConverter` utility for camelCase, snake\_case, kebab-case, etc.
* 🧰 Convenience methods: `getValueMap()`, `resolveValues()`, `canGetGetterMap()`...

---

### 🚀 Usage

Accessor maps are **cached by class name**, so the returned closures are **stateless** and require the target object to be passed as an argument:

```php
use Nandan108\PropAccess\PropAccess;

$getterMap = PropAccess::getGetterMap($myObj);
$value = $getterMap['propertyName']($myObj);

$setterMap = PropAccess::getSetterMap($myObj);
$setterMap['propertyName']($myObj, $newValue);
```

To resolve only specific properties:

```php
$getters = PropAccess::getGetterMap($myObj, ['foo_bar']);
```

#### 🧰 Convenience Utilities

**Quickly resolve values from a target object:**

```php
use Nandan108\PropAccess\PropAccess;

$values = PropAccess::getValueMap($myDto);
// → ['prop1' => 'value1', 'prop2' => 42, ...]
```

You can also resolve values from a previously obtained getter map:

```php
$getters = PropAccess::getGetterMap($entity, ['foo', 'bar']);
$values = PropAccess::resolveValues($getters, $entity);
```

---

**Check if accessors are supported for a given target:**

```php
if (PropAccess::canGetGetterMap($target)) {
    // Safe to call getGetterMap()
}
```

These methods are especially useful when working with dynamic sources, fallbacks, or introspection-based tools.

---

#### 🧐 Resolution Behavior

You can call `getGetterMap()` / `getSetterMap()` in two ways:

1. **Without property list**:
   Returns a full canonical map using camelCase keys. If both a public property (e.g. `my_prop`) and a corresponding getter (`getMyProp()`) exist, **only the getter will be included** to avoid duplication and ensure value transformation logic is preserved.

   ```php
   $map = PropAccess::getGetterMap($entity);
   $map['myProp']($entity); // uses getMyProp(), not $entity->my_prop
   ```

2. **With a property list**:
   Allows access to both public properties and getter/setter methods via multiple aliases:

   * `foo_bar` → accesses the public property (if available)
   * `fooBar` → accesses the getter/setter method (if available)

   ```php
   [$directSetter, $indirectSetter] = PropAccess::getSetterMap($myObj, ['foo_bar', 'fooBar']);

   $directSetter($myObj, 'A');   // -> $myObj->foo_bar = 'A';
   $indirectSetter($myObj, 'B'); // -> $myObj->setFooBar('B');
   ```

---

### 🔌 Custom Accessor Resolvers

Resolvers can be registered to override or extend behavior:

```php
PropAccess::bootDefaultResolvers(); // Registers built-in property/method resolvers

PropAccess::registerGetterResolver(new MyCustomGetterResolver());
PropAccess::registerSetterResolver(new MyCustomSetterResolver());
```

Later-registered resolvers are tried first. If `->supports($object)` returns false, fallback continues down the chain.

---

### 🧬 CaseConverter Utility <a id="caseconverter-utility"></a>

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

### 🔍 AccessorProxy Helper

Need array-style access to object properties? `AccessorProxy` wraps an object and exposes property access via `ArrayAccess`, `Traversable`, and `Countable`.

```php
use Nandan108\PropAccess\AccessorProxy;

$proxy = AccessorProxy::getFor($user); // read-only by default

echo $proxy['firstName'];      // -> $user->getFirstName()
$proxy['lastName'] = 'Smith';  // throws LogicException (read-only)

$rwProxy = AccessorProxy::GetFor($user, readOnly: false);
$rwProxy['lastName'] = 'Smith'; // works if setLastName() or $lastName is available
```

Includes convenience methods:

```php
$proxy->toArray(); // ['firstName' => 'John', ...]
$proxy->readableKeys();  // ['firstName', 'lastName', ...]
$proxy->writeableKeys();
```

Use `AccessorProxy::getFor()` to fail gracefully:

```php
$proxy = AccessorProxy::getFor($target);
if (!$proxy) {
    // target does not support accessors
}
```

📖 See [docs/AccessorProxy.md](docs/AccessorProxy.md) for full reference.

---

### ✅ Quality

* ✅ 100% test coverage
* ✅ Psalm level 1
* ✅ Zero dependencies

---

MIT License © [nandan108](https://github.com/nandan108)

