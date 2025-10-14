<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((["title" => ""]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((["title" => ""]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<nav
    class="fixed top-0 right-0 left-0 z-50 border-b border-zinc-200 bg-white px-4 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"
>
    <div class="flex flex-wrap items-center justify-between">
        <div class="flex items-center justify-start">
            <button
                data-drawer-target="drawer-navigation"
                data-drawer-toggle="drawer-navigation"
                aria-controls="drawer-navigation"
                class="mr-2 cursor-pointer rounded-lg p-2 text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus:bg-zinc-100 focus:ring-2 focus:ring-zinc-100 md:hidden dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-white dark:focus:bg-zinc-700 dark:focus:ring-zinc-700"
            >
                <svg aria-hidden="true" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>

                <svg aria-hidden="true" class="hidden h-6 w-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only"><?php echo e(__("navigation.header.toggle_sidebar")); ?></span>
            </button>

            <a href="/" class="mr-4 flex items-center justify-between">
                <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'mr-3 h-8 w-8 text-zinc-900 dark:text-zinc-50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-3 h-8 w-8 text-zinc-900 dark:text-zinc-50']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>

                <?php if($title): ?>
                    <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"><?php echo e($title); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="flex items-center lg:order-2">
            <?php if(session()->has("impersonating_user_id")): ?>
                <form action="<?php echo e(route("impersonate.leave")); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <button
                        type="submit"
                        class="cursor-pointer rounded-lg px-4 py-2 text-sm text-zinc-700 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 focus:ring-4 focus:ring-zinc-300 md:mr-0 dark:text-zinc-300 dark:hover:bg-zinc-700 dark:hover:text-white dark:focus:ring-zinc-600"
                    >
                        <div class="flex items-center">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-left-on-rectangle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-2 h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                            <span><?php echo e(__("navigation.header.leave_impersonation")); ?></span>
                        </div>
                    </button>
                </form>
            <?php endif; ?>

            <button
                type="button"
                class="mx-3 flex rounded-full bg-zinc-800 text-sm focus:ring-4 focus:ring-zinc-300 md:mr-0 dark:focus:ring-zinc-600"
                id="user-menu-button"
                aria-expanded="false"
                data-dropdown-toggle="user-dropdown"
            >
                <span class="sr-only"><?php echo e(__("navigation.header.open_user_menu")); ?></span>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('avatar', ['size' => 'h-8 w-8']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1539902342-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </button>

            <!-- User dropdown -->
            <div class="z-50 my-4 hidden w-56 list-none divide-y divide-zinc-100 rounded bg-white text-base shadow dark:divide-zinc-600 dark:bg-zinc-700" id="user-dropdown">
                <div class="px-4 py-3">
                    <span class="block text-sm font-semibold text-zinc-900 dark:text-white">
                        <?php echo e(auth()->user() !== null ? auth()->user()->name : __("navigation.header.guest")); ?>

                    </span>
                    <span class="block truncate text-sm text-zinc-900 dark:text-white">
                        <?php echo e(auth()->user() !== null ? auth()->user()->email : __("navigation.header.guest_email")); ?>

                    </span>
                </div>
                <ul class="py-1 text-zinc-700 dark:text-zinc-300" aria-labelledby="dropdown" >
                    <li>
                        <a href="<?php echo e(route("app.user.profile")); ?>" class="block px-4 py-2 text-sm hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-600 dark:hover:text-white">
                            <?php echo e(__("navigation.header.my_profile")); ?>

                        </a>
                    </li>
                </ul>
                <!-- End of User dropdown -->

                <!-- Logout button -->
                <ul class="py-1 text-zinc-700 dark:text-zinc-300" aria-labelledby="dropdown">
                    <li>
                        <form action="<?php echo e(route("auth.logout")); ?>" method="POST" class="w-full">
                            <?php echo csrf_field(); ?>

                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-600 dark:hover:text-white">
                                <?php echo e(__("navigation.header.sign_out")); ?>

                            </button>
                        </form>
                    </li>
                </ul>
                <!-- End of Logout button -->
            </div>
        </div>
    </div>
</nav>
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\resources\views/components/layouts/navigation/header.blade.php ENDPATH**/ ?>