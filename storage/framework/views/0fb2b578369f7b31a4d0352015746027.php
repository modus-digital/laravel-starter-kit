<?php use \App\Enums\RBAC\Permission; ?>

<aside
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-zinc-200 md:translate-x-0 dark:bg-zinc-800 dark:border-zinc-700"
    aria-label="<?php echo e(__('navigation.sidebar.label')); ?>"
    id="drawer-navigation"
>
    <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-zinc-800">
        <ul class="space-y-2">
            <?php if (isset($component)) { $__componentOriginal44ea929af5b3c862effd6826045804ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal44ea929af5b3c862effd6826045804ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.navigation.nav-link','data' => ['href' => ''.e(route('app.dashboard')).'','active' => request()->routeIs('app.dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.navigation.nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('app.dashboard')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('app.dashboard'))]); ?>
                 <?php $__env->slot('icon', null, []); ?> 
                    <svg aria-hidden="true" class="w-6 h-6 text-zinc-800 dark:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" >
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                    </svg>
                 <?php $__env->endSlot(); ?>

                <?php echo e(__('navigation.sidebar.dashboard')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal44ea929af5b3c862effd6826045804ef)): ?>
<?php $attributes = $__attributesOriginal44ea929af5b3c862effd6826045804ef; ?>
<?php unset($__attributesOriginal44ea929af5b3c862effd6826045804ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal44ea929af5b3c862effd6826045804ef)): ?>
<?php $component = $__componentOriginal44ea929af5b3c862effd6826045804ef; ?>
<?php unset($__componentOriginal44ea929af5b3c862effd6826045804ef); ?>
<?php endif; ?>
        </ul>
    </div>
</aside>
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\resources\views/components/layouts/navigation/sidebar.blade.php ENDPATH**/ ?>