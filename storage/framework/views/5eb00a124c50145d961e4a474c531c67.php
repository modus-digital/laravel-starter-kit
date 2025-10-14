<?php use \App\Enums\Settings\UserSettings; ?>
<?php use \App\Enums\Settings\Appearance; ?>

<?php
    $user = auth()->user();

    $displaySettings = collect($user->settings->where("key", UserSettings::DISPLAY)->first()->value);
    $localizationSettings = collect($user->settings->where("key", UserSettings::LOCALIZATION)->first()->value);
?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace("_", "-", $localizationSettings->get("locale"))); ?>">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <?php echo app('Illuminate\Foundation\Vite')("resources/css/app.css"); ?>

        <title>
            <?php echo e((isset($title) ? $title . " | " : "") . config("app.name")); ?>

        </title>
    </head>

    <body
        data-theme="<?php echo e($displaySettings->get("theme")); ?>"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            "bg-zinc-50 antialiased dark:bg-zinc-900",
            "dark" => $displaySettings->get("appearance") === Appearance::DARK->value,
            "system" => $displaySettings->get("appearance") === Appearance::SYSTEM->value,
        ]); ?>"
        x-data
        x-on:reload-page.window="window.location.reload()"
    >
        <?php if (isset($component)) { $__componentOriginalafb36adf865af54d1e1d61c1adc535d1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalafb36adf865af54d1e1d61c1adc535d1 = $attributes; } ?>
<?php $component = Masmerise\Toaster\ToasterHub::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toaster-hub'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Masmerise\Toaster\ToasterHub::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalafb36adf865af54d1e1d61c1adc535d1)): ?>
<?php $attributes = $__attributesOriginalafb36adf865af54d1e1d61c1adc535d1; ?>
<?php unset($__attributesOriginalafb36adf865af54d1e1d61c1adc535d1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalafb36adf865af54d1e1d61c1adc535d1)): ?>
<?php $component = $__componentOriginalafb36adf865af54d1e1d61c1adc535d1; ?>
<?php unset($__componentOriginalafb36adf865af54d1e1d61c1adc535d1); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalcfb8226f991e85e523f137ba518847e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcfb8226f991e85e523f137ba518847e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.navigation.header','data' => ['title' => ''.e($title ?? __('navigation.header.default_title')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.navigation.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($title ?? __('navigation.header.default_title')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcfb8226f991e85e523f137ba518847e1)): ?>
<?php $attributes = $__attributesOriginalcfb8226f991e85e523f137ba518847e1; ?>
<?php unset($__attributesOriginalcfb8226f991e85e523f137ba518847e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcfb8226f991e85e523f137ba518847e1)): ?>
<?php $component = $__componentOriginalcfb8226f991e85e523f137ba518847e1; ?>
<?php unset($__componentOriginalcfb8226f991e85e523f137ba518847e1); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalf0268a572fbdb05e067b5ac8f8aeec36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0268a572fbdb05e067b5ac8f8aeec36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.navigation.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.navigation.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0268a572fbdb05e067b5ac8f8aeec36)): ?>
<?php $attributes = $__attributesOriginalf0268a572fbdb05e067b5ac8f8aeec36; ?>
<?php unset($__attributesOriginalf0268a572fbdb05e067b5ac8f8aeec36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0268a572fbdb05e067b5ac8f8aeec36)): ?>
<?php $component = $__componentOriginalf0268a572fbdb05e067b5ac8f8aeec36; ?>
<?php unset($__componentOriginalf0268a572fbdb05e067b5ac8f8aeec36); ?>
<?php endif; ?>

        <main class="p-4 md:ml-64 h-auto pt-20">
            <?php echo e($slot); ?>

        </main>

        <?php echo app('Illuminate\Foundation\Vite')("resources/js/app.js"); ?>

        <?php if($displaySettings->get("appearance") === Appearance::SYSTEM->value): ?>
        <script>
            window.matchMedia('(prefers-color-scheme: dark)').matches
                ? document.documentElement.classList.add('dark')
                : document.documentElement.classList.remove('dark');
        </script>
        <?php endif; ?>
    </body>
</html>
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>