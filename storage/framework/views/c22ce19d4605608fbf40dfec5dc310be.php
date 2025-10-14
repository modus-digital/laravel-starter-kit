<?php
    $user = auth()->user();
    $hasAvatar = $user?->avatar_url ?? false;

    $sizeClasses = $size ?? 'w-16 h-16';
    $widthClass = null;
    foreach (explode(' ', $sizeClasses) as $c) {
        if (strpos($c, 'w-') === 0) { $widthClass = $c; break; }
    }
    $widthNum = $widthClass ? (int) preg_replace('/\D/', '', $widthClass) : 16;

    $fontSizeClass = 'text-xs';
    if ($widthNum >= 11 && $widthNum <= 12) { $fontSizeClass = 'text-base'; }
    elseif ($widthNum >= 13 && $widthNum <= 16) { $fontSizeClass = 'text-xl'; }
    elseif ($widthNum >= 17 && $widthNum <= 20) { $fontSizeClass = 'text-2xl'; }
    elseif ($widthNum >= 21 && $widthNum <= 24) { $fontSizeClass = 'text-3xl'; }
    elseif ($widthNum >= 25 && $widthNum <= 32) { $fontSizeClass = 'text-4xl'; }
    elseif ($widthNum > 32) { $fontSizeClass = 'text-5xl'; }
?>

<div class="flex-shrink-0" @avatar-updated.window="$wire.$refresh()">
    <!--[if BLOCK]><![endif]--><?php if(! $editable): ?>
        <!--[if BLOCK]><![endif]--><?php if($hasAvatar): ?>
            <img
                src="<?php echo e($user?->avatar_url); ?>"
                alt="<?php echo e($user?->name ?? 'User'); ?>"
                class="<?php echo e($size); ?> rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700"
            >
        <?php else: ?>
            <div class="<?php echo e($size); ?> rounded-full ring-2 ring-zinc-200 dark:ring-zinc-700 bg-zinc-100 dark:bg-zinc-800 grid place-items-center">
                <span class="text-zinc-600 dark:text-zinc-300 font-medium <?php echo e($fontSizeClass); ?> leading-none select-none">
                    <?php echo e($user?->initials()); ?>

                </span>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php else: ?>

    <button type="button" class="relative group cursor-pointer">
        <!--[if BLOCK]><![endif]--><?php if($hasAvatar): ?>
            <img
            src="<?php echo e($user?->avatar_url); ?>"
            alt="<?php echo e($user?->name ?? __('user.avatar.default_alt')); ?>"
            class="<?php echo e($size); ?> rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700">
        <?php else: ?>
            <div class="<?php echo e($size); ?> rounded-full ring-2 ring-zinc-200 dark:ring-zinc-700 bg-zinc-100 dark:bg-zinc-800 grid place-items-center">
                <span class="text-zinc-600 dark:text-zinc-300 font-medium <?php echo e($fontSizeClass); ?> leading-none select-none">
                    <?php echo e($user?->initials() ?? __('user.avatar.default_alt')); ?>

                </span>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <div @click="Livewire.dispatch('open-modal', { name: 'change-avatar' })" class="absolute inset-0 rounded-full bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="text-white text-xs font-medium text-center px-2"><?php echo e(__('user.avatar.change')); ?></span>
            </div>
        </button>

        <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'change-avatar','title' => ''.e(__('user.avatar.modal_title')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'change-avatar','title' => ''.e(__('user.avatar.modal_title')).'']); ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('user.change-avatar', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-2986661673-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\resources\views/livewire/avatar.blade.php ENDPATH**/ ?>