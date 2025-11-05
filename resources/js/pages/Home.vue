<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Head } from '@inertiajs/vue3';
import { nextTick, onMounted, ref } from 'vue';

declare const $: any;

onMounted(async () => {
    await nextTick();

    const $carousel = $('.owl-1');

    if ($carousel.length > 0) {
        const owl1 = $carousel.owlCarousel({
            center: false,
            items: 1,
            loop: true,
            stagePadding: 0,
            margin: 20,
            smartSpeed: 1000,
            autoplay: false,
            nav: false,
            dots: false,
            pauseOnHover: false,
            responsive: {
                850: {
                    margin: 20,
                    nav: false,
                    items: 1,
                },
                1000: {
                    margin: 20,
                    stagePadding: 0,
                    nav: false,
                    items: 1,
                },
                1280: {
                    margin: 20,
                    stagePadding: 0,
                    nav: false,
                    items: 2,
                },
            },
        });

        // Custom navigation buttons
        $('.bg-left-half123').on('click', () => {
            owl1.trigger('prev.owl.carousel', [1000]);
        });

        $('.bg-right-half').on('click', () => {
            owl1.trigger('next.owl.carousel', [1000]);
        });
    }
});

// -------------------- FAQ Toggle Logic --------------------
const open = ref<boolean[]>([false, false, false, false, false]);

const toggle = (index: number) => {
    open.value[index] = !open.value[index];
};
</script>

<template>
    <Head title="Home" />

    <HomeLayout>
        <div
            class="bg-[url('@/assets/bg1.jpg')] bg-cover bg-center bg-no-repeat px-5"
        >
            <div
                class="mx-auto w-full max-w-[1200px] pt-[120px] pb-18 text-center text-white"
            >
                <h1
                    class="text-3xl font-bold sm:text-4xl lg:text-5xl xl:text-6xl"
                >
                    ERP System for Car Boundary Service
                </h1>

                <div
                    class="mx-auto mt-2 flex w-full max-w-[870px] items-center gap-3 xl:max-w-[1000px]"
                >
                    <div class="h-2 w-full bg-white"></div>
                    <div>
                        <h1
                            class="text-3xl font-bold sm:text-4xl lg:text-5xl xl:text-6xl"
                        >
                            PHILIPPINES
                        </h1>
                    </div>
                    <div class="h-2 w-full bg-white"></div>
                </div>

                <p class="pt-5 pb-7 text-xl/[28px]">
                    <strong
                        >ERP System for Car Boundary Service –
                        Philippines</strong
                    >
                    brings the power of smart <br class="hidden lg:inline" />
                    technology to every trip. Whether you’re a
                    <strong>franchise owner, driver, or passenger</strong>, we
                    make
                    <br class="hidden lg:inline" />
                    transportation seamless, transparent, and eco-friendly.
                </p>

                <div class="grid justify-center gap-5 pt-7 md:flex xl:gap-7">
                    <button
                        class="text-brand-blue flex items-center gap-4 rounded-md bg-white px-7 py-2 text-xl sm:text-2xl xl:px-12"
                    >
                        <img src="@/assets/book.png" class="h-8" alt="" />
                        Book a Ride
                    </button>

                    <button
                        class="text-brand-blue flex items-center gap-4 rounded-md bg-white px-7 py-2 text-xl sm:text-2xl xl:px-12"
                    >
                        <img src="@/assets/join.png" class="h-8" alt="" />
                        Join as a Franchise
                    </button>

                    <button
                        class="text-brand-blue flex items-center gap-4 rounded-md bg-white px-7 py-2 text-xl sm:text-2xl xl:px-12"
                    >
                        <img src="@/assets/become.png" class="h-8" alt="" />
                        Become a Driver
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white px-5 py-3 text-center">
            <p class="text-brand-blue text-md font-bold">
                Empowering Every Ride. Connecting Driver, Franchisees, and
                Passenger Through Smart Mobility.
            </p>
        </div>

        <div
            id="about"
            class="bg-brand-blue scroll-mt-15 px-5 py-3 text-center"
        >
            <p class="text-2xl font-bold text-white">ABOUT US</p>
        </div>

        <div class="px-5 py-10">
            <div class="text-center">
                <p class="text-2xl/[28px] font-bold">
                    OUR MISSIONS TO
                    <span class="text-brand-blue"
                        >MODERNIZE THE BOUNDARY <br />SYSTEM - ONE RIDE AT A
                        TIME</span
                    >
                </p>
            </div>
        </div>

        <div class="mx-auto w-full max-w-[1320px] px-5 pb-10">
            <div class="grid grid-cols-12 gap-5 pb-2 md:gap-3">
                <div
                    class="col-span-12 rounded-2xl bg-[url('@/assets/about1.png')] bg-cover bg-center bg-no-repeat p-5 sm:p-9 md:col-span-8"
                >
                    <h1 class="text-2xl font-bold text-white">Our Vision</h1>
                    <p class="text-md pt-3 text-white">
                        To be the leading smart mobility ERP in the Philippines.
                        empowering transport operators through technology.
                    </p>

                    <h1 class="pt-5 text-2xl font-bold text-white">
                        Our Mission
                    </h1>

                    <div class="grid grid-cols-12 gap-3 pt-3">
                        <div
                            class="text-brand-blue col-span-12 rounded-md bg-white p-3 sm:col-span-6"
                        >
                            Digitize and simplify boundary <br />
                            operations
                        </div>

                        <div
                            class="text-brand-blue col-span-12 rounded-md bg-white p-3 sm:col-span-6"
                        >
                            Support electric vehicle adoption and sustainability
                        </div>

                        <div
                            class="text-brand-blue col-span-12 rounded-md bg-white p-3 sm:col-span-6"
                        >
                            Empower drivers and franchise owners with
                            transparent data
                        </div>

                        <div
                            class="text-brand-blue col-span-12 rounded-md bg-white p-3 sm:col-span-6"
                        >
                            Provide safe. efficient, and eco-friendly transport
                            for passenger
                        </div>
                    </div>
                </div>

                <div
                    class="shadow-blue col-span-12 rounded-2xl bg-white p-6 sm:p-8 md:col-span-4"
                >
                    <h1 class="text-brand-blue text-3xl font-bold">
                        Core Values
                    </h1>

                    <div class="mt-5 flex items-center gap-3">
                        <img src="@/assets/c1.png" class="h-8" alt="" />
                        <p class="text-brand-blue text-xl font-semibold">
                            Innovation
                        </p>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <img src="@/assets/c2.png" class="h-8" alt="" />
                        <p class="text-brand-blue text-xl font-semibold">
                            Innovation
                        </p>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <img src="@/assets/c3.png" class="h-8" alt="" />
                        <p class="text-brand-blue text-xl font-semibold">
                            Innovation
                        </p>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <img src="@/assets/c4.png" class="h-8" alt="" />
                        <p class="text-brand-blue text-xl font-semibold">
                            Service to the Filipino Commuter
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            id="works"
            class="scroll-mt-13 bg-[url('@/assets/about1.png')] bg-cover bg-center bg-no-repeat px-5 py-10"
        >
            <div class="mx-auto w-full max-w-[1320px]">
                <div class="flex items-center gap-2 sm:gap-5">
                    <h1 class="text-3xl font-bold whitespace-nowrap text-white">
                        HOW IT WORK?
                    </h1>
                    <div class="h-1 w-full bg-white"></div>
                </div>

                <div class="mt-10 grid grid-cols-12 gap-5 pb-3">
                    <div
                        class="col-span-12 rounded-xl bg-white p-5 sm:col-span-6 sm:p-6 lg:col-span-4"
                    >
                        <p class="font-bold">STEP 1</p>
                        <p class="text-brand-blue pt-3 text-2xl font-bold">
                            CHOOSE YOUR ROLE
                        </p>
                        <p>
                            Select whether you're a
                            <strong
                                >Franchise, Driver, Technician, or
                                Passenger.</strong
                            >
                        </p>
                    </div>

                    <div
                        class="col-span-12 rounded-xl bg-white p-5 sm:col-span-6 sm:p-6 lg:col-span-4"
                    >
                        <p class="font-bold">STEP 2</p>
                        <p class="text-brand-blue pt-3 text-2xl font-bold">
                            REGISTER & VERIFY
                        </p>
                        <p>
                            Submit your details, upload the necessary documents,
                            and wwait for verification
                        </p>
                    </div>

                    <div
                        class="col-span-12 rounded-xl bg-white p-5 sm:col-span-6 sm:p-6 lg:col-span-4"
                    >
                        <p class="font-bold">STEP 3</p>
                        <p class="text-brand-blue pt-3 text-2xl font-bold">
                            ACCESS YOUR DASHBOARD
                        </p>
                        <p>
                            Once approved, you can log in to your dashboard to
                            manage your vehicles, trips, and payments
                        </p>
                    </div>

                    <div
                        class="col-span-12 rounded-xl bg-white p-5 sm:col-span-6 sm:p-6 lg:col-span-4"
                    >
                        <p class="font-bold">STEP 4</p>
                        <p class="text-brand-blue pt-3 text-2xl font-bold">
                            DOWNLOAD APP
                        </p>
                        <p>Log in using your account.</p>
                    </div>

                    <div
                        class="col-span-12 rounded-xl bg-white p-5 sm:col-span-6 sm:p-6 lg:col-span-4"
                    >
                        <p class="font-bold">STEP 5</p>
                        <p class="text-brand-blue pt-3 text-2xl font-bold">
                            START OPERATING
                        </p>
                        <p>
                            Everthing happens in real-time, GPS tracking,
                            maintenance alerts, trip earning, and boundary
                            payments.
                        </p>
                    </div>

                    <div
                        class="col-span-12 grid place-items-center rounded-xl border-3 border-white p-6 sm:col-span-6 lg:col-span-4"
                    >
                        <p class="pt-3 text-2xl font-bold text-white">
                            LEARN MORE
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="franchise" class="scroll-mt-14 px-5 py-12">
            <div class="mx-auto w-full max-w-[1320px]">
                <div class="grid grid-cols-12 items-center gap-5">
                    <div class="col-span-12 md:col-span-6">
                        <h1 class="text-brand-blue text-4xl font-bold">
                            FRANCHISE NOW!
                        </h1>
                        <h1 class="pt-4 text-4xl font-bold">
                            OWN A
                            <span class="text-brand-blue"
                                >SMART E-TAXI FLEET.</span
                            >
                            MANAGE WITH EASE.
                        </h1>
                        <p class="pt-4 text-xl">
                            As a Franchise Partner, you can oversee your
                            vehicles, driver, and boundary payments
                            effortlessly.
                        </p>
                        <p class="pt-4 text-xl">
                            With our ERP System, you get real-time monitoring,
                            financial transparency, and reliable reports.
                        </p>
                    </div>

                    <div class="relative col-span-12 md:col-span-6">
                        <button
                            class="bg-brand-green absolute end-5 top-5 rounded-xl px-7 py-2 text-white"
                        >
                            Franchise Location
                        </button>
                        <iframe
                            src="https://www.google.com/maps?q=15.126148463995765,120.59506977415994&z=17&output=embed"
                            class="border-brand-blue h-[340px] w-full max-w-3xl rounded-2xl border-4 shadow-md"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-[url('@/assets/f1.jpg')] bg-cover bg-center bg-no-repeat px-5 py-10"
        >
            <div class="mx-auto w-full max-w-[1320px]">
                <div class="grid grid-cols-12 items-center">
                    <div class="col-span-12 lg:col-span-7">
                        <div class="grid grid-cols-12 gap-5 sm:gap-3">
                            <div class="col-span-12 text-white sm:col-span-6">
                                <h1 class="text-2xl font-bold">BENEFITS:</h1>
                                <p class="pt-3 text-lg">
                                    View all fleet data in one dashboard
                                </p>
                                <p class="pt-3 text-lg">
                                    Automate boundary collection
                                </p>
                                <p class="pt-3 text-lg">
                                    Track driver performance and vehicle health
                                </p>
                                <p class="pt-3 text-lg">
                                    Access real-time GPS tracking
                                </p>
                                <p class="pt-3 text-lg">
                                    Simplify financial management
                                </p>
                            </div>

                            <div class="col-span-12 text-white sm:col-span-6">
                                <h1 class="text-2xl font-bold">
                                    REQUIREMENTS:
                                </h1>
                                <p class="pt-3 text-lg">
                                    Valid Business Permit
                                </p>
                                <p class="pt-3 text-lg">DT/SEC Registration</p>
                                <p class="pt-3 text-lg">
                                    Bank Account for Payments
                                </p>
                                <p class="pt-3 text-lg">
                                    Franchise Agreement (digital or physical)
                                </p>
                            </div>
                        </div>

                        <div class="text-center md:text-start">
                            <button
                                class="text-brand-blue mt-6 rounded-xl bg-white px-5 py-2 text-xl sm:px-12 sm:text-3xl"
                            >
                                Apply as A Franchise Partner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="driver" class="scroll-mt-14 bg-white py-10">
            <div
                class="bg-brand-blue py-2 text-center text-3xl font-bold text-white"
            >
                APPLY AS A DRIVER NOW AND START EARNING!
            </div>
        </div>

        <div
            class="bg-[url('@/assets/d1.jpg')] bg-cover bg-center bg-no-repeat px-5 py-10"
        >
            <div class="mx-auto w-full max-w-[1320px] py-8">
                <div class="grid grid-cols-12 items-center">
                    <div class="col-span-12 md:col-span-6"></div>
                    <div class="col-span-12 md:col-span-6">
                        <div class="text-white">
                            <h1 class="text-3xl font-bold">
                                DRIVE SMART. EARN FASTER.
                            </h1>
                            <p class="pt-3 text-lg">
                                Join our growing community of electric taxi
                                drivers.
                            </p>
                            <p class="pt-3 text-lg">
                                Our system helps you monitor your daily income,
                                boundary dues, and trip history, all through
                                your mobile app.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white px-5 py-14">
            <div class="mx-auto w-full max-w-[1320px]">
                <div
                    class="grid grid-cols-8 gap-5 md:grid-cols-9 xl:grid-cols-10 xl:gap-3"
                >
                    <div
                        class="border-brand-blue via-brand-blue/20 to-brand-blue/20 col-span-8 rounded-xl border-2 bg-gradient-to-b from-transparent py-10 text-center sm:col-span-4 md:col-span-3 xl:col-span-2"
                    >
                        <img
                            src="@/assets/df1.png"
                            class="mx-auto h-[150px]"
                            alt=""
                        />
                        <p
                            class="text-brand-blue pt-6 text-lg/[20px] font-bold"
                        >
                            VIEW ASSIGNED <br />
                            VEHICLE DETAIL
                        </p>
                    </div>

                    <div
                        class="border-brand-blue via-brand-blue/20 to-brand-blue/20 col-span-8 rounded-xl border-2 bg-gradient-to-b from-transparent py-10 text-center sm:col-span-4 md:col-span-3 xl:col-span-2"
                    >
                        <img
                            src="@/assets/df2.png"
                            class="mx-auto h-[150px]"
                            alt=""
                        />
                        <p
                            class="text-brand-blue pt-6 text-lg/[20px] font-bold"
                        >
                            TRACK DAILY AND <br />
                            WEEKLY EARNINGS
                        </p>
                    </div>

                    <div
                        class="border-brand-blue via-brand-blue/20 to-brand-blue/20 col-span-8 rounded-xl border-2 bg-gradient-to-b from-transparent py-10 text-center sm:col-span-4 md:col-span-3 xl:col-span-2"
                    >
                        <img
                            src="@/assets/df3.png"
                            class="mx-auto h-[150px]"
                            alt=""
                        />
                        <p
                            class="text-brand-blue pt-6 text-lg/[20px] font-bold"
                        >
                            RECEIVE <br />
                            MAINTENANCE <br />
                            ALERTS
                        </p>
                    </div>

                    <div
                        class="border-brand-blue via-brand-blue/20 to-brand-blue/20 col-span-8 rounded-xl border-2 bg-gradient-to-b from-transparent py-10 text-center sm:col-span-4 md:col-span-3 xl:col-span-2"
                    >
                        <img
                            src="@/assets/df4.png"
                            class="mx-auto h-[150px]"
                            alt=""
                        />
                        <p
                            class="text-brand-blue pt-6 text-lg/[20px] font-bold"
                        >
                            ACCESS DRIVER <br />
                            TRAINING AND <br />
                            CERTIFICATION
                        </p>
                    </div>

                    <div
                        class="border-brand-blue via-brand-blue/20 to-brand-blue/20 col-span-8 rounded-xl border-2 bg-gradient-to-b from-transparent py-10 text-center sm:col-span-4 md:col-span-3 xl:col-span-2"
                    >
                        <img
                            src="@/assets/df5.png"
                            class="mx-auto h-[150px]"
                            alt=""
                        />
                        <p
                            class="text-brand-blue pt-6 text-lg/[20px] font-bold"
                        >
                            FILE SUPPORT <br />
                            TICKETS INSTANTLY
                        </p>
                    </div>
                </div>

                <div class="pt-7 text-center sm:pt-10">
                    <button
                        class="bg-brand-blue mt-6 rounded-xl px-5 py-2 text-xl text-white sm:px-12 sm:text-3xl"
                    >
                        Register as a Driver
                    </button>
                </div>
            </div>
        </div>

        <div
            id="technician"
            class="scroll-mt-12 bg-[url('@/assets/t12.jpg')] bg-cover bg-center bg-no-repeat px-5 py-10 text-center text-white"
        >
            <h1 class="text-4xl font-bold">WANT TO BE A TECHNICIAN?</h1>
            <p class="pt-3 text-xl font-bold">
                Keeping Every Ride Running Smoothly
            </p>
        </div>

        <div class="mx-auto w-full max-w-[1320px] px-5 py-12">
            <p class="text-brand-blue text-center text-xl">
                Technicians play a vital role in ensuring all vehicles are safe
                and operational.
            </p>
            <p class="text-brand-blue pt-3 text-center text-xl/[23px]">
                Through our platform, you can receive maintenance requests,
                update job status, and record parts <br />
                used, anytime, anywhere.
            </p>
            <p class="text-brand-blue pt-5 text-center text-3xl font-bold">
                BENEFITS:
            </p>

            <div class="grid grid-cols-12 gap-5 pt-7">
                <div class="col-span-12 lg:col-span-6">
                    <div
                        class="relative w-full rounded-xl border-[2px] border-[#AFC5CA] bg-[#EFF8FF] py-3 ps-8 pe-3"
                    >
                        <div
                            class="hide bg-brand-green absolute start-[-15px] top-1/2 grid h-[35px] w-[35px] -translate-y-1/2 place-items-center rounded-full border-3 border-white shadow xl:block"
                        >
                            <div class="pt-[3px]">
                                <i class="fa-solid fa-check text-white"></i>
                            </div>
                        </div>

                        <p class="text-brand-blue text-xl font-bold">
                            VIEW ASSIGNED SERVICE TICKETS
                        </p>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <div
                        class="relative w-full rounded-xl border-[2px] border-[#AFC5CA] bg-[#EFF8FF] py-3 ps-8 pe-3"
                    >
                        <div
                            class="hide bg-brand-green absolute start-[-15px] top-1/2 grid h-[35px] w-[35px] -translate-y-1/2 place-items-center rounded-full border-3 border-white shadow xl:block"
                        >
                            <div class="pt-[3px]">
                                <i class="fa-solid fa-check text-white"></i>
                            </div>
                        </div>

                        <p class="text-brand-blue text-xl font-bold">
                            UPDATED JOB PROGRESS IN REAL TIME
                        </p>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <div
                        class="relative w-full rounded-xl border-[2px] border-[#AFC5CA] bg-[#EFF8FF] py-3 ps-8 pe-3"
                    >
                        <div
                            class="hide bg-brand-green absolute start-[-15px] top-1/2 grid h-[35px] w-[35px] -translate-y-1/2 place-items-center rounded-full border-3 border-white shadow xl:block"
                        >
                            <div class="pt-[3px]">
                                <i class="fa-solid fa-check text-white"></i>
                            </div>
                        </div>

                        <p class="text-brand-blue text-xl font-bold">
                            LOG SPARE PARTS AND EXPENSES
                        </p>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <div
                        class="relative w-full rounded-xl border-[2px] border-[#AFC5CA] bg-[#EFF8FF] py-3 ps-8 pe-3"
                    >
                        <div
                            class="hide bg-brand-green absolute start-[-15px] top-1/2 grid h-[35px] w-[35px] -translate-y-1/2 place-items-center rounded-full border-3 border-white shadow xl:block"
                        >
                            <div class="pt-[3px]">
                                <i class="fa-solid fa-check text-white"></i>
                            </div>
                        </div>

                        <p class="text-brand-blue text-xl font-bold">
                            COMMUNICATE WITH FLEET MANAGERS EASILY
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-7 text-center sm:pt-10">
                <button
                    class="bg-brand-blue mt-6 rounded-xl px-5 py-2 text-xl text-white sm:px-12 sm:text-3xl"
                >
                    Join as a Technician
                </button>
            </div>
        </div>

        <div
            id="passenger"
            class="scroll-mt-12 bg-[url('@/assets/p1.jpg')] bg-cover bg-center bg-no-repeat px-5 py-12"
        >
            <div class="mx-auto w-full max-w-[1320px]">
                <div class="grid grid-cols-12 items-center">
                    <div class="col-span-12 xl:col-span-6">
                        <div class="text-white">
                            <h1 class="text-4xl font-bold">
                                FOR OUR DAILY COMMUTER!
                            </h1>
                            <h1 class="pt-3 text-2xl">
                                Ride Safe. Ride Smart.
                            </h1>
                            <p class="pt-3 text-lg">
                                Experience modern e-taxi commuting, fast,
                                secure, and environment-friendly.
                            </p>
                            <p class="pt-3 text-lg">
                                Track your driver in real time, pay cashless,
                                and rate every trip for better service.
                            </p>
                        </div>

                        <div
                            class="via-brand-green/50 to-brand-green mt-5 bg-gradient-to-l from-transparent p-2 text-2xl text-white"
                        >
                            <p>Instant ride booking</p>
                        </div>

                        <div
                            class="via-brand-green/50 to-brand-green mt-4 bg-gradient-to-l from-transparent p-2 text-2xl text-white"
                        >
                            <p>Real-time tracking and afre estimate</p>
                        </div>

                        <div
                            class="via-brand-green/50 to-brand-green mt-4 bg-gradient-to-l from-transparent p-2 text-2xl text-white"
                        >
                            <p>e-Receipts and trip history</p>
                        </div>

                        <div
                            class="via-brand-green/50 to-brand-green mt-4 bg-gradient-to-l from-transparent p-2 text-2xl text-white"
                        >
                            <p>Support center and feedback tools</p>
                        </div>

                        <div class="pt-7 text-center sm:pt-10 xl:text-start">
                            <button
                                class="text-brand-blue mt-6 rounded-xl bg-white px-5 py-2 text-xl sm:px-12 sm:text-3xl"
                            >
                                Book a Ride Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="testi" class="scroll-mt-12 px-5 py-10">
            <div class="mx-auto w-full max-w-[1320px]">
                <div class="flex items-center gap-5">
                    <h1
                        class="text-brand-blue text-3xl font-bold whitespace-nowrap"
                    >
                        TESTIMONIALS
                    </h1>
                    <div class="bg-brand-blue h-1 w-full"></div>
                </div>

                <div
                    class="grid grid-cols-12 items-center gap-5 pt-14 lg:gap-0"
                >
                    <div class="col-span-12 lg:col-span-5">
                        <h1 class="text-brand-blue text-3xl font-bold">
                            REAL STORIES FROM OOUR <br />
                            DRIVERS, FRANCHISEES, <br />
                            AND PASSENGERS
                        </h1>

                        <p class="pt-6 text-lg/[22px]">
                            Hear how ERP System for Car Boundary <br />Service-
                            Philippines is transforming the way <br />
                            Filipinos drive, manage, and ride every day.
                        </p>
                        <p class="pt-6 text-lg/[22px]">
                            From franchise owner to daily commuters, <br />
                            our system empowers everone in the <br />
                            boundary ecosystem.
                        </p>

                        <div class="pt-10">
                            <button
                                class="bg-brand-green rounded-xl px-12 py-3 text-2xl text-white md:text-3xl"
                            >
                                FEEDBACK
                            </button>
                        </div>
                    </div>

                    <div class="col-span-12 lg:col-span-7">
                        <div class="relative">
                            <div
                                class="absolute start-[-20px] top-1/2 z-10 -translate-y-1/2"
                            >
                                <button
                                    class="bg-brand-blue bg-left-half123 h-[40px] w-[40px] rounded-[50%] border-2 border-white text-white"
                                >
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                            </div>

                            <div
                                class="absolute end-[-20px] top-1/2 z-10 -translate-y-1/2"
                            >
                                <button
                                    class="bg-brand-blue bg-right-half h-[40px] w-[40px] rounded-[50%] border-2 border-white text-white"
                                >
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>

                            <div class="owl-carousel owl-1">
                                <div
                                    class="border-brand-blue rounded-xl border-4 p-5"
                                >
                                    <div
                                        class="grid items-center justify-center gap-5 sm:flex sm:justify-start"
                                    >
                                        <div
                                            class="flex justify-center sm:justify-start"
                                        >
                                            <div
                                                class="border-brand-blue h-[120px] w-[120px] overflow-hidden rounded-full border-4"
                                            >
                                                <img
                                                    src="@/assets/tes11.png"
                                                    alt=""
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                        </div>

                                        <div class="text-center sm:text-start">
                                            <h1
                                                class="text-brand-blue text-xl font-bold"
                                            >
                                                MARIA SANTOS
                                            </h1>
                                            <p>Passenger (Pasig City)</p>
                                            <div class="flex gap-2 pt-4">
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>5.0</div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="pt-4 text-lg/[22px]">
                                        Safe at maayos yung biyahe ko gamit ang
                                        e-taxi nila. Real-time ko nakikita kung
                                        nasaan yung driver at magkano ang
                                        pamasahe bago pa ako sumakay. Madali rin
                                        magbayad gamit and GCash! Sobrang
                                        convenient lalo na pag rush hour.
                                    </p>
                                </div>

                                <div
                                    class="border-brand-blue rounded-xl border-4 p-5"
                                >
                                    <div
                                        class="grid items-center justify-center gap-5 sm:flex sm:justify-start"
                                    >
                                        <div
                                            class="flex justify-center sm:justify-start"
                                        >
                                            <div
                                                class="border-brand-blue h-[120px] w-[120px] overflow-hidden rounded-full border-4"
                                            >
                                                <img
                                                    src="@/assets/tes11.png"
                                                    alt=""
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                        </div>

                                        <div class="text-center sm:text-start">
                                            <h1
                                                class="text-brand-blue text-xl font-bold"
                                            >
                                                MARIA SANTOS
                                            </h1>
                                            <p>Passenger (Pasig City)</p>
                                            <div class="flex gap-2 pt-4">
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <i
                                                        class="fa-solid fa-star text-2xl text-yellow-400"
                                                    ></i>
                                                </div>
                                                <div>5.0</div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="pt-4 text-lg/[22px]">
                                        Safe at maayos yung biyahe ko gamit ang
                                        e-taxi nila. Real-time ko nakikita kung
                                        nasaan yung driver at magkano ang
                                        pamasahe bago pa ako sumakay. Madali rin
                                        magbayad gamit and GCash! Sobrang
                                        convenient lalo na pag rush hour.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-brand-blue w-full py-2"></div>

        <div class="mx-auto w-full max-w-[780px] px-5 py-12">
            <h1 class="text-brand-blue text-center text-3xl font-bold">
                JOIN THE FUTURE OF SMART BOUNDARY SERVICE
            </h1>

            <p class="pt-4 text-center">
                Whether you're a <strong>driver, passenger,</strong> or
                <strong>franchise owner,</strong> you can experience the <br />
                convenience and reliability of our digital platform.
            </p>

            <h1 class="text-brand-blue pt-4 text-center text-3xl font-bold">
                BE PART OF OUR GROWING COMMUNITY TODAY!
            </h1>

            <div class="px-7">
                <div
                    class="grid items-center justify-center gap-4 pt-7 md:flex md:justify-between md:gap-0"
                >
                    <div>
                        <h1
                            class="text-brand-blue pt-4 text-center text-2xl font-bold"
                        >
                            DRIVER APP:
                        </h1>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-center gap-4"
                    >
                        <img
                            src="@/assets/play store.png"
                            class="h-[50px] w-auto sm:h-[60px]"
                            alt="Play Store"
                        />
                        <img
                            src="@/assets/app store.png"
                            class="h-[50px] w-auto sm:h-[60px]"
                            alt="App Store"
                        />
                    </div>
                </div>

                <div
                    class="grid items-center justify-center gap-4 pt-5 md:flex md:justify-between md:gap-0"
                >
                    <div>
                        <h1
                            class="text-brand-blue pt-4 text-center text-2xl font-bold"
                        >
                            PASSENGER APP:
                        </h1>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-center gap-4"
                    >
                        <img
                            src="@/assets/play store.png"
                            class="h-[50px] w-auto sm:h-[60px]"
                            alt="Play Store"
                        />
                        <img
                            src="@/assets/app store.png"
                            class="h-[50px] w-auto sm:h-[60px]"
                            alt="App Store"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div id="faq" class="scroll-mt-12 bg-[#e3f0fa] px-5">
            <div class="mx-auto w-full max-w-[1320px] py-10">
                <h1 class="text-brand-blue text-center text-3xl font-bold">
                    FREQUENTLY ASKED QUESTIONS (FAQ'S)
                </h1>

                <div
                    class="border-brand-blue mt-7 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-bold">
                            <span class="text-brand-blue">Q1:</span> What is ERP
                            System for Car Boundary Service - Philippines?
                        </h1>
                        <button
                            @click="toggle(0)"
                            class="bg-brand-green hidden rounded-md px-5 py-2 whitespace-nowrap text-white transition-all duration-300 md:block"
                        >
                            {{ open[0] ? 'Read Less' : 'Read More' }}
                        </button>
                    </div>
                    <transition name="slide-fade">
                        <p v-if="open[0]" class="mt-2 ps-10 text-gray-800">
                            It’s a digital platform that manages franchise,
                            driver, and passenger data in one ERP system,
                            designed for electric taxi and boundary operations.
                        </p>
                    </transition>

                    <p class="mt-2 block text-gray-800 md:hidden">
                        It’s a digital platform that manages franchise, driver,
                        and passenger data in one ERP system, designed for
                        electric taxi and boundary operations.
                    </p>
                </div>

                <div
                    class="border-brand-blue mt-4 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-bold">
                            <span class="text-brand-blue">Q2:</span> How can I
                            apply as a driver?
                        </h1>
                        <button
                            @click="toggle(1)"
                            class="bg-brand-green hidden rounded-md px-5 py-2 text-white transition-all duration-300 md:block"
                        >
                            {{ open[1] ? 'Read Less' : 'Read More' }}
                        </button>
                    </div>
                    <transition name="slide-fade">
                        <p v-if="open[1]" class="mt-2 ps-10 text-gray-800">
                            Visit the Driver page, fill out the registration
                            form, and submit your driver’s license and ID. Once
                            verified, you’ll get your login details via email.
                        </p>
                    </transition>
                    <p class="mt-2 block text-gray-800 md:hidden">
                        Visit the Driver page, fill out the registration form,
                        and submit your driver’s license and ID. Once verified,
                        you’ll get your login details via email.
                    </p>
                </div>

                <div
                    class="border-brand-blue mt-4 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-bold">
                            <span class="text-brand-blue">Q3:</span> Do you
                            support electric vehicles only?
                        </h1>
                        <button
                            @click="toggle(2)"
                            class="bg-brand-green hidden rounded-md px-5 py-2 text-white transition-all duration-300 md:block"
                        >
                            {{ open[2] ? 'Read Less' : 'Read More' }}
                        </button>
                    </div>
                    <transition name="slide-fade">
                        <p v-if="open[2]" class="mt-2 ps-10 text-gray-800">
                            Our system is optimized for e-vehicles, but
                            traditional vehicles can also be registered while
                            transitioning to green fleets.
                        </p>
                    </transition>
                    <p class="mt-2 block text-gray-800 md:hidden">
                        Our system is optimized for e-vehicles, but traditional
                        vehicles can also be registered while transitioning to
                        green fleets.
                    </p>
                </div>

                <div
                    class="border-brand-blue mt-4 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-bold">
                            <span class="text-brand-blue">Q4:</span> Can
                            passengers book rides directly?
                        </h1>
                        <button
                            @click="toggle(3)"
                            class="bg-brand-green hidden rounded-md px-5 py-2 text-white transition-all duration-300 md:block"
                        >
                            {{ open[3] ? 'Read Less' : 'Read More' }}
                        </button>
                    </div>
                    <transition name="slide-fade">
                        <p v-if="open[3]" class="mt-2 ps-10 text-gray-800">
                            Yes, passengers can book rides using the mobile app
                            or the booking form on our website.
                        </p>
                    </transition>
                    <p class="mt-2 block text-gray-800 md:hidden">
                        Yes, passengers can book rides using the mobile app or
                        the booking form on our website.
                    </p>
                </div>

                <div
                    class="border-brand-blue mt-4 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-bold">
                            <span class="text-brand-blue">Q5:</span> Is my data
                            secure?
                        </h1>
                        <button
                            @click="toggle(4)"
                            class="bg-brand-green hidden rounded-md px-5 py-2 text-white transition-all duration-300 md:block"
                        >
                            {{ open[4] ? 'Read Less' : 'Read More' }}
                        </button>
                    </div>
                    <transition name="slide-fade">
                        <p v-if="open[4]" class="mt-2 ps-10 text-gray-800">
                            Absolutely. We comply with the Philippine Data
                            Privacy Act and all your information is encrypted.
                        </p>
                    </transition>
                    <p class="mt-2 block text-gray-800 md:hidden">
                        Absolutely. We comply with the Philippine Data Privacy
                        Act and all your information is encrypted.
                    </p>
                </div>

                <h1
                    id="Terms"
                    class="text-brand-blue scroll-mt-10 pt-12 text-center text-3xl font-bold"
                >
                    TERMS & PRIVACY
                </h1>

                <div class="mt-7 bg-white px-3 py-6 text-center">
                    <p>
                        <strong
                            >ERP System for Car Boundary Service -
                            Philippines</strong
                        >
                        collects only the information necessary to provide
                        transportation and account services
                    </p>

                    <p class="pt-4">
                        We ad here to the
                        <strong>Data Privacy Act of 2012 (RA 10173)</strong> and
                        do not share your data with third parties without
                        consent.
                    </p>
                </div>

                <div
                    class="border-brand-blue mt-4 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <h1 class="text-xl font-bold">
                        Users must verify their identity before full account
                        access.
                    </h1>
                </div>

                <div
                    class="border-brand-blue mt-4 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <h1 class="text-xl font-bold">
                        All payments are processed securely through authorized
                        gateways (e.g., GCash, PayMaya).
                    </h1>
                </div>

                <div
                    class="border-brand-blue mt-4 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <h1 class="text-xl font-bold">
                        By register, users agree to abide by platform rules and
                        community guidelines.
                    </h1>
                </div>

                <div
                    class="border-brand-blue mt-4 border-t-0 border-r-0 border-b-0 border-l-6 bg-white p-4"
                >
                    <h1 class="text-xl font-bold">
                        You may request deletion of your data anytime by
                        emailing privacy@carboundary.ph.
                    </h1>
                </div>
            </div>
        </div>

        <div
            id="contact"
            class="scroll-mt-12 bg-[url('@/assets/con12.jpg')] bg-cover bg-center bg-no-repeat py-10 text-center text-white"
        >
            <h1 class="text-4xl font-bold">CONTACT US</h1>
            <p class="pt-3 text-2xl">We're Here to Help You.</p>
            <p class="pt-1 text-xl">
                Have questions, suggestions, or partnership inquires?
                <br />Reach out to us anytime.
            </p>
        </div>

        <div class="mx-auto w-full max-w-[1320px] px-5 py-10">
            <div class="bg-brand-blue w-full rounded-md p-4 shadow-md sm:p-8">
                <form class="">
                    <div class="grid grid-cols-12 gap-4 md:gap-5">
                        <div class="col-span-12 md:col-span-6">
                            <label for="" class="text-white">Full Name</label>
                            <input
                                type="text"
                                placeholder="Name"
                                class="mb-3 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                            />

                            <label for="" class="text-white">Email</label>
                            <input
                                type="email"
                                placeholder="Email"
                                class="mb-3 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                            />

                            <label for="" class="text-white">Subject</label>
                            <input
                                type="text"
                                placeholder="Subject"
                                class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                            />
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label for="" class="text-white">Message</label>
                            <textarea
                                placeholder="Message.."
                                rows="5"
                                class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                            ></textarea>

                            <div class="pt-4 text-end">
                                <button
                                    type="submit"
                                    class="text-brand-blue rounded-md bg-white px-6 py-2 font-bold"
                                >
                                    Send Message
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-12 gap-5 pt-8">
                <div
                    class="bg-brand-blue col-span-12 rounded-xl p-5 sm:p-8 md:col-span-6"
                >
                    <div class="flex gap-4 sm:gap-7">
                        <div>
                            <div
                                class="grid h-[40px] w-[40px] place-items-center rounded-full bg-white"
                            >
                                <i
                                    class="fa-solid fa-phone-volume text-brand-blue text-xl"
                                ></i>
                            </div>
                        </div>

                        <div class="font-bold text-white">
                            <h1 class="text-xl">MOBILE NUMBER</h1>
                            <h1 class="text-2xl">(+63) 999-999-9999</h1>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-brand-blue col-span-12 rounded-xl p-5 sm:p-8 md:col-span-6"
                >
                    <div class="flex gap-4 sm:gap-7">
                        <div>
                            <div
                                class="grid h-[40px] w-[40px] place-items-center rounded-full bg-white"
                            >
                                <i
                                    class="fa-solid fa-clock text-brand-blue text-2xl"
                                ></i>
                            </div>
                        </div>

                        <div class="text-white">
                            <h1 class="text-xl font-bold">SUPPORT HOURS</h1>
                            <h1 class="text-lg">
                                Monday - Saturday, 8:00 AM - 6:00 PM
                            </h1>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-brand-blue col-span-12 rounded-xl p-5 sm:p-8 md:col-span-6"
                >
                    <div class="flex gap-4 sm:gap-7">
                        <div>
                            <div
                                class="grid h-[40px] w-[40px] place-items-center rounded-full bg-white"
                            >
                                <i
                                    class="fa-solid fa-envelope text-brand-blue text-2xl"
                                ></i>
                            </div>
                        </div>

                        <div class="text-white">
                            <h1 class="text-xl font-bold">EMAIL</h1>
                            <h1 class="text-lg">support@carboundary.ph</h1>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-brand-blue col-span-12 rounded-xl p-5 sm:p-8 md:col-span-6"
                >
                    <div class="flex gap-4 sm:gap-7">
                        <div>
                            <div
                                class="grid h-[40px] w-[40px] place-items-center rounded-full bg-white"
                            >
                                <i
                                    class="fa-solid fa-location-dot text-brand-blue text-2xl"
                                ></i>
                            </div>
                        </div>

                        <div class="text-white">
                            <h1 class="text-xl font-bold">ADDRESS</h1>
                            <h1 class="text-lg">
                                Unit 202, GreenTech Building, Quezon City,
                                Philippines
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Start Footer -->
        <div class="bg-brand-blue py-8">
            <div class="mx-auto w-full max-w-[1320px] px-3 sm:px-5">
                <div class="grid grid-cols-12 gap-7">
                    <div class="col-span-12 pe-8 sm:pe-0 lg:col-span-6">
                        <div class="grid items-center gap-5 sm:flex">
                            <div
                                class="text-brand-blue grid h-[70px] w-[150px] place-items-center border-1 border-black bg-white text-2xl"
                            >
                                LOGO
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white">
                                    ERP SYSTEM FOR CAR BOUNDARY SERVICE -
                                    PHILIPPINES
                                </h1>
                            </div>
                        </div>
                        <p class="pt-3 text-xl text-white">
                            Empowering Every Ride. Connecting Drivers,
                            Franchisees, and Passenger Through Smart Mobility.
                        </p>

                        <div class="pt-4">
                            <div class="dm:gap-4 flex gap-3">
                                <img
                                    src="@/assets/fb.png"
                                    class="h-[48px] rounded-full border-2 border-white"
                                    alt=""
                                />
                                <img
                                    src="@/assets/yt.png"
                                    class="h-[48px] rounded-full border-2 border-white"
                                    alt=""
                                />
                                <img
                                    src="@/assets/instagram.png"
                                    class="h-[48px] rounded-full border-2 border-white"
                                    alt=""
                                />
                                <img
                                    src="@/assets/x.png"
                                    class="h-[48px] rounded-full border-2 border-white"
                                    alt=""
                                />
                                <img
                                    src="@/assets/tiktok.png"
                                    class="h-[48px] rounded-full border-2 border-white"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 lg:col-span-6 lg:px-10">
                        <h3 class="text-xl font-bold text-white">
                            Quick Links
                        </h3>
                        <div class="grid grid-cols-12 gap-4 pt-3">
                            <div class="col-span-6 sm:col-span-4">
                                <a
                                    href="#"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    Home
                                </a>

                                <br />
                                <a
                                    href="#about"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    About Us</a
                                >
                                <br />
                                <a
                                    href="#works"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    How It Works</a
                                >

                                <br />
                                <a
                                    href="#franchise"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    Franchise</a
                                >
                            </div>

                            <div class="col-span-6 sm:col-span-4">
                                <a
                                    href="#driver"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    Driver</a
                                >
                                <br />

                                <a
                                    href="#technician"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    Technician</a
                                >
                                <br />

                                <a
                                    href="#passenger"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    Passenger</a
                                >
                                <br />

                                <a
                                    href="#testi"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    Testimonials</a
                                >
                            </div>

                            <div class="col-span-12 sm:col-span-4">
                                <a
                                    href="#faq"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    FAQ's</a
                                >
                                <br />
                                <a
                                    href="#Terms"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    Terms & Privacy</a
                                >
                                <br />
                                <a
                                    href="#contact"
                                    class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                                >
                                    Contact Us</a
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Start Footer -->

        <!-- Start All Rights Reserved -->
        <div
            class="text-brand-blue bg-white px-5 py-3 text-center text-lg/[25px]"
        >
            <p>
                © 2025 ERP System for Car Boundary Service - Philippines <br />
                "Smart Mobility for Every Filipino."
            </p>
        </div>
        <!-- End All Rights Reserved -->
    </HomeLayout>
</template>

<style scoped>
/* Smooth slide transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.4s ease;
    overflow: hidden;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    max-height: 0;
    transform: translateY(-5px);
}
.slide-fade-enter-to,
.slide-fade-leave-from {
    opacity: 1;
    max-height: 200px;
    transform: translateY(0);
}
</style>
