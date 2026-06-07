# PMToolBox

PMToolBox is a small PocketMine-MP toolbox library. It provides utilities for manager-based plugin structure, task helpers and an optional RCON interface.

## Setup

Register the toolbox from your plugin lifecycle:

```php
use pocketmine\plugin\PluginBase;
use valres\toolbox\ToolboxLoader;

final class Main extends PluginBase {
    protected function onLoad(): void {
        ToolboxLoader::load($this);
    }

    protected function onEnable(): void {
        ToolboxLoader::enable($this);
    }

    protected function onDisable(): void {
        ToolboxLoader::disable();
    }
}
```

By default, managers are loaded from the `manager` directory under your plugin namespace.

If your managers are elsewhere:

```php
ToolboxLoader::load($this, "modules");
ToolboxLoader::enable($this);
```

If you do not want to use the manager system:

```php
ToolboxLoader::load($this, loadManagers: false);
ToolboxLoader::enable($this, enableManagers: false);
```

## Managers

Create managers by extending `BaseManager`.

```php
namespace myplugin\manager;

use valres\toolbox\manager\BaseManager;
use valres\toolbox\manager\attribute\Manager;
use valres\toolbox\manager\attribute\ManagerPriority;
use valres\toolbox\manager\attribute\ManagerPriorityEnum;

#[Manager(name: "Economy", version: "1.0.0")]
#[ManagerPriority(ManagerPriorityEnum::PRIORITY_HIGH)]
final class EconomyManager extends BaseManager {
    public function onLoad(): void {
        // Light startup work.
    }

    public function init(): void {
        // Enable-time setup.
    }

    public function save(): void {
        // Persist data before shutdown.
    }
}
```

## Dependencies

Use `ManagerDependsOn` when a manager must be initialized after another one.

```php
use valres\toolbox\manager\attribute\ManagerDependsOn;

#[ManagerDependsOn(["Economy"])]
final class ShopManager extends BaseManager {
    // ...
}
```

The resolver detects duplicate manager names, missing dependencies and circular dependencies.

## Auto Register

Managers can auto-register commands and listeners from folders next to the manager class.

```php
use valres\toolbox\manager\attribute\AutoRegister;
use valres\toolbox\manager\attribute\AutoRegisterAll;
use valres\toolbox\manager\attribute\AutoRegisterType;

#[AutoRegister(AutoRegisterType::COMMANDS)]
#[AutoRegister(AutoRegisterType::LISTENERS)]
final class GameplayManager extends BaseManager {
    // ...
}

#[AutoRegisterAll]
final class ProfileManager extends BaseManager {
    // Registers command/ and listener/.
}
```

Expected folder layout:

```text
src/myplugin/manager/ProfileManager.php
src/myplugin/manager/command/ProfileCommand.php
src/myplugin/manager/listener/ProfileListener.php
```

## Access

```php
$economy = ToolboxLoader::getManager("Economy");
$shop = ToolboxLoader::getManagerOf(ShopManager::class);

if (ToolboxLoader::hasManager("Economy")) {
    // ...
}
```

You can also access the handler directly:

```php
$handler = ToolboxLoader::getManagerHandler();
```

Each manager also behaves like a singleton automatically. You do not need to add a trait in your manager class:

```php
$economy = EconomyManager::getInstance();

if (EconomyManager::hasInstance()) {
    EconomyManager::getInstance()->save();
}
```

The singleton instance is registered when the manager is discovered and reset when managers are disabled.

## RCON

RCON is optional and can be started during enable:

```php
use valres\toolbox\rcon\RconSettings;

ToolboxLoader::enable($this, new RconSettings(
    address: "0.0.0.0",
    port: 19132,
    password: "secret"
));
```

Or with the helper:

```php
ToolboxLoader::enable($this, RconSettings::default("secret", 19132));
```

## Packets

PMToolBox includes a small packet API inspired by LibPacket/SimplePacketHandler: register handlers for specific packet classes instead of writing long `instanceof` chains.

### Monitor

Use a monitor when you only want to observe packets. Return values are ignored.

```php
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use valres\toolbox\ToolboxLoader;

$monitor = ToolboxLoader::createPacketMonitor();

$monitor->monitorIncoming(function(LoginPacket $packet, NetworkSession $session): void {
    // Debug or inspect the packet.
});
```

### Interceptor

Use an interceptor when you want to cancel or modify packets before normal handling. Return `false` to cancel the packet event.

```php
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use valres\toolbox\ToolboxLoader;

$interceptor = ToolboxLoader::createPacketInterceptor();

$interceptor->interceptIncoming(function(AdventureSettingsPacket $packet, NetworkSession $session): bool {
    return true; // false cancels the DataPacketReceiveEvent.
});
```

### Handler Classes

```php
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\Packet;
use valres\toolbox\packet\PacketHandlerInterface;

final class LoginPacketHandler implements PacketHandlerInterface {
    public function getPacketIds(): array {
        return [LoginPacket::class];
    }

    public function handle(Packet $packet, NetworkSession $session): bool {
        return true;
    }
}

ToolboxLoader::createPacketInterceptor()->registerIncoming(new LoginPacketHandler());
```

### Attributes

You can also register public methods with attributes.

```php
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use valres\toolbox\packet\attribute\IncomingPacket;
use valres\toolbox\packet\attribute\OutgoingPacket;
use valres\toolbox\packet\attribute\PacketHandler;
use valres\toolbox\packet\PacketDirection;

final class PacketHandlers {
    #[IncomingPacket(LoginPacket::class)]
    public function onLogin(LoginPacket $packet, NetworkSession $session): bool {
        return true;
    }

    #[OutgoingPacket]
    public function onAnyOutgoing(LoginPacket $packet, NetworkSession $session): void {
    }

    #[PacketHandler(PacketDirection::INCOMING, LoginPacket::class)]
    public function onLoginWithGenericAttribute(LoginPacket $packet, NetworkSession $session): bool {
        return true;
    }
}

ToolboxLoader::createPacketInterceptor()->registerAnnotated(new PacketHandlers());
```
