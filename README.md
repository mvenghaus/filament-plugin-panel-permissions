# Filament Plugin - Panel Permissions

## This is a working concept and should be used in production!

I tried the available plugins to handle permissions in Filament. But none of them could fulfil my wishes.

This plugin is ready to work, so please check it out and give me some feedback.

## The Problems

These are the problems I faced and tried to solve. But maybe I was just to stupid to get it to work.
My following solutions are a concept and might be not the best idea. Any feedback is welcome!

### Problem | Roles with panel access

In Filament Shield you can define a "super_admin" and a "panel_user". They can be configured in the config file.
But if I add an additional role the system doesn't know whether it is a Filament role or not.
I have to adjust the "canAccessPanel" method.

#### Solution

I added a new guard to the "auth.php" config file. With this it is clear that this is a filament role.

```php
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'filament' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
```

### Problem | Duplicate Policy & Permission Names

I manage all my resources in separate packages. There is often the case that I use the same model name.
Usually no problem because of the namespace. But in the existing plugins they work with class shortname.

```
/vendor/mvenghaus/package-shop/src/Models/Product.php
/vendor/mvenghaus/package-pim/src/Models/Product.php

-> get the same names
```

#### Solution

I use the full qualified class name for the policy files and the permission names.

```
/vendor/mvenghaus/package-shop/src/Models/Product.php
/vendor/mvenghaus/package-pim/src/Models/Product.php

->

/app/Policies/Mvenghaus/PackageShop/Models/Product.php
/app/Policies/Mvenghaus/PackagePim/Models/Product.php

permission name would be:

mvenghaus-packageshop-models-product::viewAny
```

With this structure the auth provider cannot guess the policies.
So I bind the model to the policy in the FilamentPlugin.php.

### Problem | Package Policies

This is not really a plugin issue more a laravel problem. But I think there must already be a good solution, but I couldn't find one.

If you have a plugin, I believe the plugin itself knows its own policies best.
But when I create a Policy inside my package the auth provider can guess it and it will be processed.

But I don't know whether the plugin user is using (Spatie) Laravel Permissions.
If not, it results in a 403 error.

#### Solution

To solve this I integrated something called ```Lazy Polices```.

So instead of this file:
```
/vendor/mvenghaus/package-shop/src/Policies/ProductPolicy.php
```

You add "Lazy" to the filename.
```
/vendor/mvenghaus/package-shop/src/Policies/ProductLazyPolicy.php
```

This style can't be guessed by the auth handler.
But when you use this module it binds this policy the model.

### Problem | Decoupling custom permissions

When you build a plugin with custom permissions Filament Shield for example provides a way by implementing an interface.

```php
class PostResource extends Resource implements HasShieldPermissions
```

Doing this in a plugin will force the plugin user to have filament shield installed.
But who knows whether the user uses shield or any permission handling!?

### Solution

Since we can define policies in our package using lazy policies the creator of the plugin has a location where he can define them.

Here I use an annotation to declare a method to a permission.

```php
class PostLazyPolicy
{
    use HandlesAuthorization;

    /** @policyAction custom-name-of-permission::publish */
    public function publish(User $user): bool
    {
        return $user->can('custom-name-of-permission::publish');
    }
}
```