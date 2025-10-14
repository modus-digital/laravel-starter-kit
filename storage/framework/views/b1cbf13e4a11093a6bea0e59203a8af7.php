<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    "href" => "#",
    "active" => false,
    "icon" => null,
    "label" => null,
    "children" => [],
]));

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

foreach (array_filter(([
    "href" => "#",
    "active" => false,
    "icon" => null,
    "label" => null,
    "children" => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<li class="w-full">
    <?php if(count($children) > 0): ?>
        <button
            type="button"
            class="group flex w-full items-center rounded-lg p-2 text-base font-medium text-zinc-900 transition duration-75 hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-700"
            aria-controls="dropdown-<?php echo e(Str::slug($label)); ?>"
            data-collapse-toggle="dropdown-<?php echo e(Str::slug($label)); ?>"
        >
            <?php if(isset($icon)): ?>
                <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
            <?php endif; ?>

            <span class="ml-3 flex-1 text-left whitespace-nowrap">
                <?php echo e($label); ?>

            </span>
        </button>

        <ul
            id="dropdown-<?php echo e(Str::slug($label)); ?>"
            class="hidden space-y-2 py-2"
        >
            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <a
                        href="<?php echo e($child["href"]); ?>"
                        class="group flex w-full items-center rounded-lg p-2 pl-11 text-base font-medium text-zinc-900 transition duration-75 hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-700"
                    >
                        <?php echo e($child["label"] ?? $slot); ?>

                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php else: ?>
        <a
            href="<?php echo e($href); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                "group flex items-center rounded-lg p-2 text-base font-medium text-zinc-900 hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-700 dark:hover:text-white",
                "bg-zinc-100 dark:bg-zinc-700 dark:text-white dark:hover:bg-zinc-700 dark:hover:text-white" => $active,
            ]); ?>"
        >
            <?php if(isset($icon)): ?>
                <?php echo e($icon); ?>

            <?php endif; ?>

            <span class="ml-3"><?php echo e($label ?? $slot); ?></span>
        </a>
    <?php endif; ?>
</li>
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\resources\views/components/layouts/navigation/nav-link.blade.php ENDPATH**/ ?>