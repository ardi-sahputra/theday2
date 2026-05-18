<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template><div class="th-paper-grain" aria-hidden="true"/></template>

<style scoped>
.th-paper-grain {
    position: fixed; inset: 0;
    background-image: url('/images/templates/treasure-hunt/paper-grain.svg');
    background-size: 400px 400px; background-repeat: repeat;
    pointer-events: none; mix-blend-mode: multiply; opacity: 0.5;
    animation: th-grain-shimmer 14s linear infinite; z-index: 5;
}
@keyframes th-grain-shimmer { 0% { background-position: 0 0 } 100% { background-position: 400px 400px } }
@media (prefers-reduced-motion: reduce) { .th-paper-grain { animation: none; } }
</style>
