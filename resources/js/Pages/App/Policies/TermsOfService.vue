<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { usePage } from '@inertiajs/vue3';
import { route } from 'momentum-trail';
</script>

<template>
    <AppHead pageTitle="Terms Of Service" :description="'Review our Terms of Service (TOS) for' +  usePage<any>().props.site.production.domains.main + 'here.'"
        :url="route('Welcome')" />
    <Navbar :landing="false" />
    <Sidebar>
        <div class="cell medium-7">
            <div class="text-center card">
                <div class="card-body">
                    Terms of Service (TOS) for {{ usePage<any>().props.site.production.domains.main }}
                    <div class="text-2xl text-info"><i class="fad fa-information"></i>Last Updated: 26th Febuary 2025</div>

                    1. Acceptance of Terms:
                    By accessing and using {{ usePage<any>().props.site.production.domains.main }} (the "Site"), you ("User," "you," or "your") agree to comply with
                    and be bound by these Terms of Service ("TOS"). If you do not agree to these TOS, please do not use
                    the Site.

                    2. Description of Service:

                    {{ usePage<any>().props.site.production.domains.main }} is a community-run online gaming platform that allows Users to create, share, and play
                    games and experiences. We strive to provide a moderated and safe environment for our Users.


                    3. User Accounts:
                    Eligibility: You must be at least [Insert Age, e.g., 13] years old to create an account. If you are
                    under [Insert Age], you must have parental or guardian consent.
                    Account Responsibility: You are responsible for maintaining the confidentiality of your account
                    credentials and for all activities that occur under your account.  
                    Accurate Information: You agree to provide accurate and complete information when creating your
                    account.
                    Account Termination: We reserve the right to suspend or terminate your account for violations of
                    these TOS or for any other reason at our discretion.

                    4. User Content:
                    Ownership: You retain ownership of the content you create and upload to the Site ("User Content").
                    License to {{ usePage<any>().props.site.production.domains.main }}: By uploading User Content, you grant {{ usePage<any>().props.site.production.domains.main }} a non-exclusive, worldwide,
                    royalty-free license to use, reproduce, modify, distribute, and display your User Content for the
                    purpose of operating and promoting the Site.  
                    Responsibility: You are solely responsible for your User Content and the consequences of posting or
                    publishing it.
                    Prohibited Content: You agree not to upload or share content that is:
                    Illegal, harmful, or abusive.
                    Infringing on intellectual property rights.
                    Hateful, discriminatory, or offensive.
                    Sexually explicit or exploitative.
                    Spam or unauthorized advertising.
                    Content that could be used to phish, scam, or otherwise defraud other users.

                    5. Moderation:
                    {{ usePage<any>().props.site.production.domains.main }} has a dedicated moderation team to ensure a safe and respectful community.
                    We reserve the right to remove User Content, suspend accounts, and take other actions to enforce
                    these TOS.
                    User reports are taken seriously, and action will be taken based on the reported content.

                    6. Intellectual Property:
                    Site Content: All content on the Site, excluding User Content, is owned by {{ usePage<any>().props.site.production.domains.main }} or its
                    licensors and is protected by intellectual property laws.
                    Castilla License: The source code for {{ usePage<any>().props.site.production.domains.main }} is licensed under the Castilla License
                    (https://github.com/CastillaSBC/Castilla-License). By using this site you also agree to the terms of
                    the castilla License.
                    User Content: As stated above, users retain ownership of their user created content.

                    7. Code of Conduct:
                    Respect other Users.
                    Do not engage in harassment, bullying, or other harmful behavior.
                    Follow the instructions of the moderation team.
                    Do not exploit glitches or vulnerabilities in the Site.
                    Do not attempt to reverse engineer the site.

                    8. Disclaimer of Warranties:
                    The Site is provided "as is" and "as available" without warranties of any kind.
                    We do not warrant that the Site will be uninterrupted, error-free, or secure.
                    We disclaim all warranties, express or implied, including but not limited to warranties of
                    merchantability, fitness for a particular purpose, and non-infringement.

                    9. Limitation of Liability:
                    {{ usePage<any>().props.site.production.domains.main }} shall not be liable for any direct, indirect, incidental, consequential, or punitive
                    damages arising from your use of the Site.
                    Our liability shall be limited to the maximum extent permitted by law.
                    10. Indemnification:

                    You agree to indemnify and hold harmless {{ usePage<any>().props.site.production.domains.main }} and its affiliates, officers, and employees from
                    any claims, liabilities, damages, and expenses arising from your use of the Site or your violation
                    of these TOS.

                    11. Changes to TOS:
                    We reserve the right to modify these TOS at any time.
                    We will notify Users of significant changes by posting a notice on the Site or by other means.
                    Your continued use of the Site after any changes constitutes acceptance of the new TOS.

                    12. Governing Law:
                    These TOS shall be governed by and construed in accordance with the laws of the Republic of
                    Singapore, without regard to its conflict of law principles.

                    13. Contact Information:
                    If you have any questions about these TOS, please contact us at [Insert Contact Email].
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
</template>