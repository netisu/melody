<template>
        <main class="container">
            <div class="gap-4 align-middle flex-container" style="z-index: 1; position: relative">
                <slot name="bannerAsset" />
                <div class="text-start">
                    <div class="text-2xl fw-semibold">
                        <slot name="bannerName" />
                    </div>
                    <div class="text-sm text-truncate fw-semibold text-muted">
                        <slot name="bannerDescription" />
                    </div>
                    <div class="gap-2 align-middle flex-container">
                        <slot name="bannerButtons" />
                    </div>
                </div>
            </div>
        </main>
</template>
