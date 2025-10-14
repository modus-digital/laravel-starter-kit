<!DOCTYPE html>
<html lang="<?php echo e(str_replace("_", "-", app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <?php echo app('Illuminate\Foundation\Vite')("resources/css/app.css"); ?>

        <title>
            <?php echo e((isset($title) ? $title . " | " : "") . config("app.name")); ?>

        </title>
    </head>

    <body class="bg-zinc-50 antialiased dark:bg-zinc-900">
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

        <?php echo e($slot); ?>


        <?php echo app('Illuminate\Foundation\Vite')("resources/js/app.js"); ?>
        <script>
            window.matchMedia('(prefers-color-scheme: dark)').matches
                ? document.documentElement.classList.add('dark')
                : document.documentElement.classList.remove('dark');
        </script>
    </body>
</html>
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\resources\views/components/layouts/guest.blade.php ENDPATH**/ ?>