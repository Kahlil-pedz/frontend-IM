<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BreadPitt Dashboard</title>
     <base href="/im/breadpitt/">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        .soft-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.03);
        }
        .custom-checkbox {
            appearance: none;
            width: 18px; height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            outline: none; cursor: pointer;
            transition: all 0.2s ease;
            position: relative; background-color: #ffffff;
        }
        .custom-checkbox:checked { background-color: #0f172a; border-color: #0f172a; }
        .custom-checkbox:checked::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free'; font-weight: 900;
            color: white; font-size: 10px;
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        .orange-btn-gradient {
            background: linear-gradient(135deg, #ff7e29 0%, #f95700 100%);
            box-shadow: 0 4px 14px 0 rgba(249, 87, 0, 0.3);
        }
        .orange-btn-gradient:hover {
            background: linear-gradient(135deg, #f95700 0%, #e04e00 100%);
            box-shadow: 0 6px 18px 0 rgba(249, 87, 0, 0.4);
        }
    </style>
</head>
<body class="min-h-screen p-3 sm:p-6 lg:p-8 flex items-center justify-center">

    <div class="w-full max-w-[1360px] bg-white rounded-[28px] p-4 sm:p-6 shadow-2xl border border-slate-100 flex flex-col gap-6">

        <header class="flex items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-red-500 flex items-center justify-center shadow-md shadow-orange-500/20">
                    <i class="fa-solid fa-mug-hot text-white text-lg"></i>
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-900">Bread<span class="text-orange-500">Pitt</span></span>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="showToast('No new notifications')" class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition relative">
                    <i class="fa-regular fa-bell text-sm"></i>
                    <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-orange-500 rounded-full"></span>
                </button>
                <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
                    <img src="https://tr.rbxcdn.com/180DAY-99bc9c6c9cb64b72d7039a52c27c6e90/420/420/FaceAccessory/Webp/noFilter" alt="User Avatar" class="w-10 h-10 rounded-xl object-cover ring-2 ring-slate-100">
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <section class="lg:col-span-7 flex flex-col gap-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1">
                        <button onclick="filterCategory('All', this)" class="category-pill active-pill px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-full whitespace-nowrap transition">All Items</button>
                        <button onclick="filterCategory('Coffee', this)" class="category-pill px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-full whitespace-nowrap transition">Coffee</button>
                        <button onclick="filterCategory('Bakery', this)" class="category-pill px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-full whitespace-nowrap transition">Bakery</button>
                        <button onclick="filterCategory('Desserts', this)" class="category-pill px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-full whitespace-nowrap transition">Desserts</button>
                    </div>

                    <div class="relative w-full sm:w-48">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="searchInput" oninput="filterProducts()" placeholder="Search menu..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                    </div>
                </div>

                <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"></div>
            </section>

            <section class="lg:col-span-5 bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 flex flex-col gap-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">Cart</h2>
                        <span id="cartBadgeCount" class="bg-orange-100 text-orange-600 text-xs font-bold px-2.5 py-0.5 rounded-full">0</span>
                    </div>
                    <button onclick="clearAllCart()" class="text-xs font-semibold text-orange-500 hover:text-orange-600 transition">
                        Delete All
                    </button>
                </div>

                <div id="cartList" class="flex flex-col gap-3 max-h-[380px] overflow-y-auto pr-1"></div>

                <div class="bg-white border border-slate-200/80 rounded-xl p-3 flex flex-col gap-2 shadow-sm">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-ticket text-orange-500 text-xs"></i>
                        <span class="text-xs font-bold text-slate-700">Voucher Code</span>
                        <span class="text-[10px] text-slate-400 ml-auto">(Try: <code class="bg-slate-100 px-1 py-0.5 rounded text-orange-600 font-semibold">BREAD10</code>)</span>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" id="voucherInput" placeholder="Enter voucher code..." class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs uppercase tracking-wider focus:outline-none focus:border-orange-500 transition">
                        <button id="applyVoucherBtn" onclick="applyVoucher()" class="orange-btn-gradient text-white text-xs font-bold px-4 py-2 rounded-lg transition whitespace-nowrap">
                            Apply
                        </button>
                    </div>
                    <div id="voucherStatus" class="text-[11px] font-semibold hidden"></div>
                </div>

                <div class="bg-white border border-slate-200/80 rounded-xl p-4 flex flex-col gap-2.5 shadow-sm">
                    <div class="flex justify-between items-center text-xs font-medium text-slate-500">
                        <span>Subtotal</span>
                        <span id="subtotalPrice" class="font-bold text-slate-700">₱0.00</span>
                    </div>
                    <div id="discountRow" class="flex justify-between items-center text-xs font-medium text-slate-500 hidden">
                        <span>Discount (<span id="discountPercentText">10%</span>)</span>
                        <span id="discountPrice" class="font-bold text-emerald-600">-₱0.00</span>
                    </div>
                    <div class="h-px bg-slate-100 my-1"></div>
                    <div class="flex justify-between items-center text-sm font-bold text-slate-800">
                        <span>Total Payment</span>
                        <span id="totalPaymentPrice" class="text-lg text-slate-900">₱0.00</span>
                    </div>
                </div>

                <button onclick="proceedToPayment()" class="w-full orange-btn-gradient text-white font-bold py-3.5 rounded-2xl transition text-sm flex items-center justify-center gap-2 shadow-lg shadow-orange-500/20 active:scale-[0.99]">
                    Proceed to Payment <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </section>
        </div>
    </div>

    <div id="paymentModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-sm w-full mx-4 shadow-2xl flex flex-col items-center text-center gap-4 transform scale-95 transition-transform duration-300">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-check"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-800">Order Confirmed!</h3>
                <p class="text-xs text-slate-500 mt-1">Your order has been placed successfully.</p>
            </div>
            <div id="orderModalSummary" class="w-full bg-slate-50 rounded-xl p-3 border border-slate-200/80 text-xs text-left space-y-1.5"></div>
            <button onclick="closePaymentModal()" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 rounded-xl text-xs transition">
                Back to Dashboard
            </button>
        </div>
    </div>

    <div id="toast" class="fixed bottom-5 right-5 bg-slate-900 text-white text-xs font-medium px-4 py-3 rounded-2xl shadow-xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none z-50">
        <i class="fa-solid fa-circle-info text-orange-400"></i>
        <span id="toastMessage">Notification</span>
    </div>

    <script src="app.js"></script>
</body>
</html>