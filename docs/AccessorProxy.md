# AccessorProxy

`AccessorProxy` is a utility wrapper that makes it easy to **traverse**, **read**, and optionally **write** properties on an object using array-style access and iteration.

It uses `AccessorRegistry` internally to resolve getters and setters.

```php
$proxy = AccessorProxy::getFor($user, readOnly: false);
echo $proxy['name'];       // → getName()
$proxy['email'] = 'x@y.z'; // → setEmail(), only if readOnly = false
```

---

## 🔧 Constructor

```php
new AccessorProxy(
    object $target,
    ?array $propNames = null,
    bool $readOnly = true,
    ?array $getterMap = null,
    ?array $setterMap = null,
)
```

| Param        | Type     | Description                                                      |
| ------------ | -------- | ---------------------------------------------------------------- |
| `$target`    | `object` | The object whose properties will be accessed                     |
| `$propNames` | `?array` | If set, only these properties will be exposed                    |
| `$readOnly`  | `bool`   | If true (default), disables write access (`offsetSet`)                     |
| `$getterMap` | `?array` | Optionally preload getter map                                    |
| `$setterMap` | `?array` | Optionally preload setter map (used only if `$readOnly = false`) |

---

## 📦 Static Factory: `AccessorProxy::getFor()`

```php
AccessorProxy::getFor(
    object $target,
    ?array $propNames = null,
    bool $readOnly = true,
    bool $throwOnFailure = false,
)
```

Create a proxy or fail gracefully if the target doesn’t support accessors, or if requested properties are not available:

```php
$proxy = AccessorProxy::getFor($object);
if (!$proxy) {
    // fallback logic
}
```

Supports both read-only and read-write modes:

```php
$proxy = AccessorProxy::getFor($object, ['email'], readOnly: false);
```

---

## 💡 ArrayAccess

```php
$value = $proxy['firstName']; // → getFirstName() or $firstName
$proxy['email'] = 'a@b.com';  // → setEmail() or $email (if readOnly = false)
isset($proxy['age']);         // → true if getter exists AND value is not null
unset($proxy['age']);         // ❗Throws exception — use removeAccessors(['age']) instead.
```

---

## 🔄 Traversable and Countable

```php
foreach ($proxy as $key => $val) {
    // iterate over accessible properties
}

count($proxy); // count of accessible keys
```

---

## 🛠 Utility Methods

| Method         | Returns         | Description                               |
| -------------- | --------------- | ----------------------------------------- |
| `toArray()`    | `array`         | Resolves all values as `[$key => $value]` |
| `readableKeys()` | `array<string>` | Returns all getter keys/offsets |
| `writableKeys()` | `array<string>` | Returns all setter keys/offsets |
| `getGetters()` | `array`         | Raw getter closures (keyed by property)   |
| `getSetters()` | `array\|null`   | Raw setter closures (if not read-only) |
| `removeAccessors(array $keys)` | `void` | Removes specified getters and setters from proxy's internal maps |
| `count()` <small>*(Implements `\Countable`)*</small> | `int` | Returns the number of readable keys. |

> ⚠️ `foreach($proxy as $key => $val)` // iterates over *readable* keys

> ⚠️ `unset($proxy['name'])` is **not allowed** and will throw an exception.
> Use `$proxy->removeAccessors(['name'])` to remove keys from the proxy's map without touching the object itself.

---

## 🔒 Read-Only Mode

By default, `AccessorProxy` is read-only. You can opt into read-write mode by setting `$readOnly = false`:

```php
$proxy = new AccessorProxy($user, readOnly: false); // will throw if write access is not possible
// or
$proxy = AccessorProxy::getFor($user, readOnly: false); // will return null if write access is not possible

$proxy['name'] = 'Jane';
```

---

## 🔗 Related

* [`AccessorRegistry`](./README.md#🔧-features) – the underlying accessor map manager
* [`CaseConverter`](./README.md#🧬-caseconverter-utility)

---

MIT License © [nandan108](https://github.com/nandan108)
