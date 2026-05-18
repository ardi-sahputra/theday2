<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    pageMeta:          { type: Object,  required: true }, // { key, title, section }
    pageIndex:         { type: Number,  required: true }, // 0-based
    totalPages:        { type: Number,  required: true },
    showToBeContinued: { type: Boolean, default: true },
})

const isFirst = computed(() => props.pageIndex === 0)
const isLast  = computed(() => props.pageIndex === props.totalPages - 1)
const showSticker = computed(() => props.showToBeContinued && !isFirst.value && !isLast.value)
</script>

<template>
    <article class="cb-page" :data-page-key="pageMeta.key">
        <header class="cb-page-masthead">
            <h2 class="cb-page-title">{{ pageMeta.title }}</h2>
        </header>

        <div class="cb-page-body">
            <slot/>
        </div>

        <footer class="cb-page-footer">
            <img v-if="showSticker"
                 src="/images/templates/comic-book/cb-tobe-continued.svg"
                 alt="" class="cb-page-tbc" aria-hidden="true"/>
            <span class="cb-page-num">Page {{ pageIndex + 1 }} of {{ totalPages }}</span>
        </footer>
    </article>
</template>

<style scoped>
.cb-page {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    min-height: 100dvh;
    background: #F9F4E2;
    padding: 24px 16px 80px;
    box-sizing: border-box;
    overflow: hidden;
}
@media (min-width: 768px) {
    .cb-page { padding: 36px 32px 96px; }
}
.cb-page-masthead {
    margin-bottom: 16px;
}
.cb-page-title {
    font-family: 'Bangers', 'Impact', 'Anton', sans-serif;
    font-size: 28px;
    line-height: 1;
    letter-spacing: 0.04em;
    color: #0A0A0A;
    margin: 0;
    text-transform: uppercase;
}
@media (min-width: 768px) {
    .cb-page-title { font-size: 44px; }
}
.cb-page-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.cb-page-footer {
    position: absolute;
    right: 16px;
    bottom: 56px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.cb-page-tbc {
    height: 32px;
    width: auto;
}
.cb-page-num {
    font-family: 'Bangers', 'Impact', sans-serif;
    font-size: 13px;
    letter-spacing: 0.05em;
    color: #0A0A0A;
    background: rgba(249, 244, 226, 0.85);
    padding: 4px 10px;
    border: 2px solid #0A0A0A;
}
</style>
