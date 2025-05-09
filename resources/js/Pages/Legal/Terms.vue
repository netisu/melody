<script setup lang="ts">
import AppHead from '@/Components/AppHead.vue';
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';


const site = computed(() => usePage<any>().props.site)

defineProps({
    LandingItems: { type: Object, default: null },
});
</script>
<template>
    <AppHead pageTitle="Welcome"
        description="Join in on the action today. Buy &amp; sell items, participate in spaces, make friends, and more."
        :url="route(`Welcome`)" />
    <Navbar />
    <Sidebar>
        Terms of Service

        1. Introduction

        Welcome to {{ site.name }} ("{{ site.name }}" or "we" or "us"). These Terms of Service ("Terms") govern your
        access to and use of the website located at
        https://developer.mozilla.org/en-US/docs/Learn/Common_questions/Web_mechanics/What_is_a_URL (the "Site") and all
        associated services (collectively, the "Services").

        2. Acceptance of Terms

        By accessing or using the Services, you ("you" or "user") agree to be bound by these Terms. If you disagree with
        any part of the Terms, then you may not access or use the Services.

        3. Modifications to the Terms

        We may revise and update these Terms at our sole discretion. We will use reasonable efforts to provide you with
        notice of any material changes to the Terms. You are advised to review the Terms periodically for any changes.
        Your continued access to or use of the Services after the revised Terms have been posted constitutes your
        acceptance of the revised Terms.

        4. Use of the Services

        4.1. Permitted Uses

        You may use the Services only for lawful purposes and in accordance with these Terms. You agree not to use the
        Services:

        In any way that violates any applicable law or regulation in any jurisdiction where you access or use the
        Services.
        For the purpose of exploiting, harming, or disrupting the Services or the servers or networks connected to the
        Services.
        To transmit, store, or upload any content that is infringing, obscene, defamatory, threatening, or otherwise
        unlawful.
        To interfere with or disrupt the use of the Services by any other user.
        4.2. User Content

        You may create, post, or upload content ("User Content") to the Services. You retain all ownership rights in
        your User Content. However, by posting User Content to the Services, you grant us a non-exclusive, royalty-free,
        worldwide license to use, reproduce, modify, publish, and distribute your User Content in connection with the
        Services.

        5. Intellectual Property

        The Services and all content and materials included on the Site, including, without limitation, text, graphics,
        logos, icons, images, videos, software, and other materials (collectively, the "Content"), are the intellectual
        property of {{ site.name }} or its licensors. You may not use the Content for any commercial purpose without our
        express written consent.

        6. Disclaimer of Warranties

        THE SERVICES ARE PROVIDED "AS IS" AND WITHOUT ANY WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED. WE DISCLAIM ALL
        WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
        PURPOSE, AND NON-INFRINGEMENT. WE DO NOT WARRANT THAT THE SERVICES WILL BE UNINTERRUPTED OR ERROR-FREE, THAT
        DEFECTS WILL BE CORRECTED, OR THAT THE SERVICES OR THE SERVER THAT MAKES THE SERVICES AVAILABLE ARE FREE OF
        VIRUSES OR OTHER HARMFUL COMPONENTS.

        7. Limitation of Liability

        TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, WE SHALL NOT BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
        SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES ARISING OUT OF OR IN ANY WAY CONNECTED WITH YOUR USE OF THE
        SERVICES, EVEN IF WE HAVE BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.

        8. Termination

        We may terminate your access to the Services at any time, for any reason or no reason, without notice.

        9. Governing Law and Dispute Resolution

        9.1. Choice of Law

        These Terms shall be governed by and construed in accordance with the laws of [Neutral Location], without regard
        to its conflict of law provisions. You agree that any legal action or proceeding arising out of or relating to
        these Terms will be brought exclusively in the courts of [Neutral Location], and you consent to the exclusive
        jurisdiction and venue of such courts.

        9.2. Amicable Resolution

        In the event of a dispute arising out of or relating to these Terms, the parties agree to attempt to resolve the
        dispute amicably through negotiations. If the parties are unable to reach a resolution within a reasonable time
        period, the dispute shall be settled by [Arbitration Institution] in accordance with its arbitration rules. The
        arbitration shall be conducted in [Neutral Location] and the language of the arbitration shall be English.

        10. Entire Agreement

        These Terms constitute the entire agreement between you and us regarding your use of the Services.
    </Sidebar>
</template>