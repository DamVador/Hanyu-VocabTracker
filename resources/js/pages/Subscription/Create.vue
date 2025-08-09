<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { loadStripe } from '@stripe/stripe-js';

defineOptions({ layout: AuthenticatedLayout });

const pageProps = usePage().props;
// On récupère directement la prop 'isPremium' passée par le contrôleur
const isPremium = ref(pageProps.isPremium);
const isLoading = ref(false);

const subscribe = async () => {
    isLoading.value = true;
    try {
        const stripe = await loadStripe(pageProps.stripeKey);
        const { error } = await stripe.redirectToCheckout({
            lineItems: [{
                price: "price_1RqxrZGinDxnXQFiAkEWVkEj",
                quantity: 1,
            }],
            mode: 'subscription',
            successUrl: route('subscription.success'),
            cancelUrl: route('subscription.cancel'),
        });
        if (error) {
            console.error(error);
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head title="Subscription Plans" />

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-screen-xl 2xl:max-w-screen-2xl">
        <div class="text-center mb-10 md:mb-12">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-800">
                Subscription Plans
            </h2>
            <p class="text-base sm:text-lg text-gray-600 mt-2 max-w-2xl mx-auto">
                Choose the plan that's right for you.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <!-- Free Plan Card -->
            <div class="bg-white overflow-hidden shadow-md rounded-lg relative transition-all duration-300 transform hover:scale-105"
                 :class="{ 'border-2 border-indigo-600': !isPremium }">
                <div v-if="!isPremium" class="ribbon"><span>Your Current Plan</span></div>
                <div class="p-8">
                    <h3 class="text-3xl font-bold text-gray-900 mb-2">Free</h3>
                    <p class="text-5xl font-extrabold text-gray-800 mb-6">0 EUR<span class="text-xl text-gray-500 font-medium"> / month</span></p>

                    <ul class="text-left mb-8 space-y-3">
                        <li class="flex items-center text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>100 Words Dictionary Limit</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>5 Study Sessions Limit</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Basic Analytics</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span>Detailed Progress Analytics</span>
                        </li>
                    </ul>

                    <button v-if="!isPremium" class="w-full bg-indigo-50 text-indigo-700 font-semibold py-3 px-6 rounded-md cursor-default">
                        Current Plan
                    </button>
                    <Link v-else :href="route('subscription.cancel')" class="w-full inline-flex justify-center items-center py-3 px-6 bg-gray-200 text-gray-800 font-semibold rounded-md hover:bg-gray-300">
                        Downgrade
                    </Link>
                </div>
            </div>

            <!-- Premium Plan Card -->
            <div class="bg-white overflow-hidden shadow-md rounded-lg relative transition-all duration-300 transform hover:scale-105"
                 :class="{ 'border-2 border-indigo-600': isPremium }">
                <div v-if="isPremium" class="ribbon"><span>Your Current Plan</span></div>
                <div class="p-8">
                    <h3 class="text-3xl font-bold text-gray-900 mb-2">Premium</h3>
                    <p class="text-5xl font-extrabold text-indigo-600 mb-6">4.90 EUR<span class="text-xl text-gray-500 font-medium"> / month</span></p>

                    <ul class="text-left mb-8 space-y-3">
                        <li class="flex items-center text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Unlimited Word Dictionary</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Unlimited Study Sessions</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Basic Analytics</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Detailed Progress Analytics</span>
                        </li>
                    </ul>
                    
                    <a v-if="isPremium" :href="route('billing-portal')" target="_blank"
                       class="w-full inline-flex justify-center items-center py-3 px-6 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700">
                       Manage Subscription
                    </a>
                    <button v-else @click="subscribe" :disabled="isLoading"
                            class="w-full inline-flex justify-center items-center py-3 px-6 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700"
                            :class="{ 'opacity-50 cursor-not-allowed': isLoading }">
                        <span v-if="isLoading">Redirecting...</span>
                        <span v-else>Upgrade to Premium</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ribbon {
    position: absolute;
    top: 20px;
    right: -20px;
    z-index: 10;
    width: 140px;
    height: 32px;
    background-color: #4338ca; /* Indigo-700 */
    color: white;
    font-size: 13px;
    font-weight: 600;
    line-height: 32px;
    text-align: center;
    transform: rotate(45deg);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.ribbon span {
    display: block;
    width: 100%;
}
</style>
