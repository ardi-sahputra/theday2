<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PasswordGate       from './PasswordGate.vue';
import InvitationRenderer from '@/Components/invitation/InvitationRenderer.vue';
import { isSceneFillTemplate } from '@/Components/invitation/templates/registry';

const props = defineProps({
    invitation:    { type: Object,  required: true },
    messages:      { type: Array,   default: () => [] },
    needPassword:  { type: Boolean, default: false },
    isPreview:     { type: Boolean, default: false },
    guest:         { type: Object,  default: null },
    showWatermark: { type: Boolean, default: false },
});

// Full-bleed scene templates sit behind the floating glass banner (no spacer);
// normal templates get a spacer so their content isn't hidden under it.
const isScene = computed(() => isSceneFillTemplate(props.invitation.template_slug));

// Locked invitations ship only the gate payload; unlocking reloads the page
// so the server (now session-unlocked) returns the full invitation.
const unlocked = computed(() => ! props.needPassword);

function onUnlocked() {
    router.reload();
}
</script>

<template>
    <Head>
        <title>{{ isPreview ? `[Preview] ${invitation.title}` : invitation.title }}</title>
        <meta name="description" :content="`Undangan ${invitation.title}`"/>
        <meta property="og:title"  :content="invitation.title"/>
        <meta property="og:type"   content="website"/>
        <meta v-if="invitation.details?.cover_photo_url"
              property="og:image"  :content="invitation.details.cover_photo_url"/>
        <meta v-if="isPreview" name="robots" content="noindex"/>
    </Head>

    <!-- Preview banner -->
    <div v-if="isPreview"
         style="position:fixed;top:0;left:0;right:0;z-index:9999;background:rgba(15,20,17,0.45);backdrop-filter:blur(20px) saturate(1.6);-webkit-backdrop-filter:blur(20px) saturate(1.6);border-bottom:1px solid rgba(255,255,255,0.14);color:#EAF0EC;font-family:Arial,sans-serif;font-size:13px;font-weight:600;line-height:1.5;text-align:center;padding:9px 16px;text-shadow:0 1px 3px rgba(0,0,0,0.55);box-shadow:0 2px 12px rgba(0,0,0,0.2);">
        <span style="color:#B7CDBF;">👁</span>
        Mode Preview — undangan masih <strong style="color:#fff;">Draft</strong>, belum dipublikasikan.
        <a :href="`/dashboard/invitations/${invitation.id}/customize-v2`"
           style="display:inline-block;vertical-align:middle;margin-left:6px;padding:3px 12px;background:rgba(146,168,156,0.9);color:#fff;border-radius:8px;text-decoration:none;font-size:12px;white-space:nowrap;text-shadow:none;">
            Edit →
        </a>
    </div>

    <!-- Spacer agar konten tidak tertutup banner (scene full-bleed: banner
         mengambang sebagai liquid glass, jadi tak perlu spacer). -->
    <div v-if="isPreview && !isScene" style="height:41px;"></div>

    <!-- Password gate -->
    <PasswordGate
        v-if="!unlocked"
        :slug="invitation.slug"
        :primary-color="invitation.config?.primary_color ?? '#92A89C'"
        :font-family="invitation.config?.font_title ?? invitation.config?.font ?? 'Playfair Display'"
        :cover-url="invitation.details?.cover_photo_url"
        @unlocked="onUnlocked"
    />

    <!-- Full invitation renderer -->
    <InvitationRenderer
        v-else
        :invitation="invitation"
        :messages="messages"
        :guest="guest"
        :is-demo="false"
        :auto-open="false"
    />

    <!-- Watermark badge for free tier (fixed overlay, independent of v-if/v-else chain) -->
    <a
        v-if="showWatermark && unlocked"
        href="/"
        target="_blank"
        rel="noopener"
        style="position:fixed;bottom:16px;right:16px;z-index:9998;display:flex;align-items:center;gap:6px;background:rgba(255,255,255,0.92);backdrop-filter:blur(8px);border:1px solid rgba(0,0,0,0.08);border-radius:999px;padding:6px 12px 6px 8px;text-decoration:none;box-shadow:0 2px 12px rgba(0,0,0,0.12);font-family:Arial,sans-serif;"
    >
        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="#92A89C" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        <span style="font-size:11px;font-weight:600;color:#4B5563;white-space:nowrap;">Dibuat dengan <span style="color:#73877C;">TheDay</span></span>
    </a>
</template>

<style>
:root { --inv-primary: #92A89C; --inv-font: 'Playfair Display', serif; }
.reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.7s ease, transform 0.7s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }
.envelope-leave-active { transition: opacity 0.6s ease, transform 0.6s ease; }
.envelope-leave-to     { opacity: 0; transform: scale(1.04); }
@keyframes spin-slow { to { transform: rotate(360deg); } }
.animate-spin-slow { animation: spin-slow 4s linear infinite; }
</style>
