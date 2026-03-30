# Symcon 9.0 Breaking Changes & Migration Guide

## 1. Configurator `create` must be an array (parent chain)

**Before:**
```php
"create" => [
    "moduleID" => $moduleID,
    "configuration" => [...]
]
```

**After:**
```php
"create" => [
    ["moduleID" => $moduleID, "configuration" => [...]],  // device
    ["moduleID" => "{splitter-guid}"]                     // parent splitter
]
```

Without this, creating an instance from the Configurator fails with:
> _"Die erstellte Instanz wird nicht als Liste erstellt ... Daher kann keine gültige Verbindung bestimmt werden."_

---

## 2. `SendDataToChildren` loopback is blocked

Inside `ForwardData()`, calling `SendDataToChildren()` **no longer routes back to the child that originated the `SendDataToParent()` call**. The data is silently dropped for the originating child.

**Fix:** Return the data directly from `ForwardData()` and have the calling child process it itself:

```php
// In Splitter::ForwardData() — return the packet instead of broadcasting it back
$packet = XDeviceDataPacket::fromPayload($device, XEventTrigger::MessageQueue);
return strval($packet); // returned to the child that called SendDataToParent()

// In Device::SyncValuesFromCloud() — process the returned packet directly
$packetJSON = $this->SendDataToParent(json_encode($data));
if (!empty($packetJSON)) {
    $this->ReceiveData($packetJSON);
}
```

---

## 3. `RegisterTimer` script text is never updated after first creation

`RegisterTimer` is fully idempotent — if the timer already exists, the script text is **never overwritten**, even if you call `RegisterTimer` again with a different script. Additionally, `$_IPS['SELF']` is `0` in timer execution context (not the instance ID).

**Fix:** Avoid timers for deferred one-shot calls. Use `IPS_RunScriptText` with the instance ID hardcoded at call time:

```php
IPS_RunScriptText('sleep(1); KnockautX_SyncValuesFromCloud(' . $this->InstanceID . ');');
```

This spawns a new PHP process immediately, sleeps, then calls the function — non-blocking for the caller.

---

## 4. Splitter must call `SetStatus(IS_ACTIVE)` in `ApplyChanges`

Without it, `HasActiveParent()` on child devices always returns `false`, even when the splitter is fully configured and connected.

**Fix:** Add to splitter's `ApplyChanges()`:
```php
public function ApplyChanges()
{
    parent::ApplyChanges();
    $this->SetStatus(IS_ACTIVE);
}
```

---

## 5. `IPS_GetChildrenIDs` ≠ module-connected children

`IPS_GetChildrenIDs($splitterID)` returns **object tree** children (instances placed under the splitter in the IPS object tree), **not** instances connected via `ConnectParent()`.

**Fix:** To find all instances connected to a splitter via `ConnectParent`:
```php
foreach (IPS_GetInstanceList() as $id) {
    if (IPS_GetInstance($id)['ConnectionID'] === $this->InstanceID) {
        // this instance is connected to this splitter
    }
}
```

---

## 6. `GetIDForIdent` now throws a PHP warning for missing idents

Previously silent when the ident didn't exist, it now emits a PHP warning. This is especially problematic in `TransformValue()` which runs before variables are guaranteed to exist.

**Fix:** Suppress with `@` and guard the result:
```php
$varID = @$this->GetIDForIdent('some_ident');
if ($varID) {
    // safe to use
}
```

---

## 7. `$this->SetValue(ident)` vs global `SetValue(id)`

These two functions have different signatures:
- `$this->SetValue(string $ident, mixed $value)` — module method, takes a **string ident**
- `SetValue(int $variableID, mixed $value)` — global function, takes an **integer variable ID**

Passing an integer ID to `$this->SetValue()` causes Symcon to look for an object with ident `"12345"` and fail with a warning. Previously this may have been tolerated; in 9.0 it is a warning.

**Fix:** Use the correct function for what you have:
```php
// If you have an ident string:
$this->SetValue('switch_led', true);

// If you have a variable ID integer:
$varID = @$this->GetIDForIdent('switch_led');
if ($varID) SetValue($varID, true);
```
