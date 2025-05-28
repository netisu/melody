<script setup lang="ts">
import Navbar from '@/Components/LayoutParts/Navbar.vue';
import Sidebar from '@/Components/LayoutParts/Sidebar.vue';
import Footer from '@/Components/LayoutParts/Footer.vue';
import { usePage } from '@inertiajs/vue3';
import { route } from "ziggy-js";
</script>

<template>
    <AppHead pageTitle="Terms Of Service" :description="'Review our Terms of Service (TOS) for' +  usePage<any>().props.site.production.domains.main + 'here.'"
        :url="route('welcome.page')" />
    <Navbar :landing="false" />
    <Sidebar>
        <div class="cell medium-7">
            <div class="text-center card">
                <div class="card-body">
## Privacy Policy for {{ usePage<any>().props.site.name }}
                    <div class="text-2xl text-info"><i class="fad fa-information"></i>Last Updated: 28th May 2025</div>
At {{ usePage<any>().props.site.name }}, we are committed to protecting your privacy while providing a valuable and respectful online experience. This Privacy Policy outlines how we collect, use, and safeguard your information when you visit and interact with our website.

---

### Information We Collect

We want to be clear about what information we might collect and why.

* **Non-Personal Information:** When you visit our site, we may automatically collect certain non-personal information. This typically includes:
    * **Browser and Device Information:** Such as your IP address, browser type, operating system, and screen resolution.
    * **Usage Data:** Details about how you interact with our website, like the pages you visit, the time spent on those pages, and the links you click.
    * **Referral Information:** The website you came from to reach our site.
    We collect this data using common technologies like cookies and analytics tools (e.g., Google Analytics) to understand website traffic, improve our content, and enhance your user experience. This information does not directly identify you.

* **Personal Information (If Applicable):** We generally **do not** ask for personal information directly unless you voluntarily provide it for a specific purpose, such as:
    * Submitting a contact form with your name and email address.
    * Creating an account (if that feature is available).
    * Subscribing to a newsletter.
    If you provide personal information, we will only use it for the purpose it was given (e.g., to respond to your inquiry, send you updates).

---

### How We Use Your Information

The information we collect helps us to:

* **Operate and Maintain Our Website:** Ensure our site functions correctly and efficiently.
* **Improve User Experience:** Understand how visitors use our site so we can make it more intuitive and helpful.
* **Analyze Trends:** Monitor website performance and identify popular content.
* **Respond to Your Inquiries:** If you contact us, we'll use your information to reply.
* **Maintain a Respectful Environment:** As outlined in our rules, we reserve the right to review any user-submitted content to ensure it aligns with our guidelines and to address any violations.

---

### User-Generated Content and Your Privacy

Our website may allow users to post comments, forum discussions, or other content. Please be aware of the following:

* **Public Display:** Any information you post in public areas of our website (e.g., comments section) may be visible to others.
* **Prohibited Content:** We strictly prohibit the posting of:
    * **Personal Information of Others:** You must **never** post personal information about another individual without their express permission. This includes names, images, locations, or anything relating to their real-life likeness.
    * **Graphic or Illegal Content:** This includes content related to minors, violence, or animal abuse. Such content will be reported to the appropriate authorities.
    * **Hate Speech or Discrimination:** Any comments or content that is racist, discriminatory, harassing, or bullying based on race, religion, sexual orientation, or any other characteristic is strictly forbidden.
    * **NSFW Content:** Our website is not for explicit content.
    * **Harmful Conduct:** Harassing, threatening, or doxxing any user is unacceptable.
* **Moderation:** We actively moderate user-generated content to ensure it aligns with our **Rules & Guidelines**. Content that violates these rules may be removed, and repeat offenders may have their access revoked.

---

### Cookies and Tracking Technologies

We use cookies and similar tracking technologies to enhance your experience on our website.

* **What are cookies?** Cookies are small text files placed on your device by websites you visit. They are widely used to make websites work more efficiently, as well as to provide information to the site owners.
* **How we use them:** We use cookies to remember your preferences, understand how you interact with our site, and improve its functionality.
* **Your choices:** You can control and/or delete cookies as you wish. Most web browsers allow you to manage your cookie preferences. However, disabling cookies may affect the functionality of our website.

---

### Third-Party Links

Our website may contain links to other websites that are not operated by us. We have no control over, and assume no responsibility for, the content, privacy policies, or practices of any third-party sites or services. We encourage you to review the privacy policies of any third-party websites you visit.

---

### Data Security

We implement reasonable security measures to protect your information from unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet or method of electronic storage is 100% secure, so we cannot guarantee absolute security.

---

### Changes to This Privacy Policy

We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page. We encourage you to review this Privacy Policy periodically for any changes.

---

### Contact Us

If you have any questions about this Privacy Policy or our data practices, please contact us at {{ usePage<any>().props.site.production.domains.support }}.

---
                </div>
            </div>
        </div>
    </Sidebar>
    <Footer />
    </template>