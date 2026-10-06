let products       = [];
let cartItems      = [];
let appliedVoucher = null;
let discountRate   = 0;
let activeCategory = 'All';

/* ---------- Fetch products from PHP ---------- */
async function loadProducts() {
    const res  = await fetch('api/product.php');
    const data = await res.json();
    products   = data.products || [];
    renderProducts();
}

function renderProducts(productsToRender = products) {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = '';

    let filtered = productsToRender;
    if (activeCategory !== 'All') {
        filtered = productsToRender.filter(p => p.category === activeCategory);
    }

    if (filtered.length === 0) {
        grid.innerHTML = `<div class="col-span-full py-12 text-center text-slate-400 text-xs">No items found</div>`;
        return;
    }

    filtered.forEach(prod => {
        const card = document.createElement('div');
        card.className = "soft-card rounded-2xl p-3 flex flex-col justify-between transition-all hover:shadow-md group";

        card.innerHTML = `
            <div class="relative w-full h-36 rounded-xl overflow-hidden mb-3 bg-slate-100">
                <img src="${prod.image}" alt="${prod.name}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <button onclick="toggleLike(${prod.id})" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/90 backdrop-blur-sm shadow-sm flex items-center justify-center text-slate-400 hover:text-red-500 transition">
                    <i class="${prod.liked ? 'fa-solid fa-heart text-red-500' : 'fa-regular fa-heart'} text-xs"></i>
                </button>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-xs tracking-tight leading-snug line-clamp-1">${prod.name}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">${prod.specs}</p>
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-baseline gap-1.5">
                        <span class="font-extrabold text-orange-500 text-sm">₱${prod.price.toFixed(2)}</span>
                        <span class="text-[10px] text-slate-400 line-through">₱${prod.originalPrice.toFixed(2)}</span>
                    </div>
                    <button onclick="addToCartFromGrid(${prod.id})" class="w-7 h-7 rounded-lg orange-btn-gradient text-white flex items-center justify-center transition transform active:scale-90 shadow-sm">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });
}

/* ---------- Cart ---------- */
function renderCart() {
    const list = document.getElementById('cartList');
    list.innerHTML = '';
    document.getElementById('cartBadgeCount').innerText = cartItems.length;

    if (cartItems.length === 0) {
        list.innerHTML = `
            <div class="py-12 flex flex-col items-center justify-center text-slate-400 gap-2">
                <i class="fa-solid fa-basket-shopping text-2xl text-slate-300"></i>
                <p class="text-xs font-semibold">Your cart is empty</p>
            </div>`;
        updateTotals();
        return;
    }

    cartItems.forEach(item => {
        const itemTotal = item.price * item.qty;
        const row = document.createElement('div');
        row.className = "bg-white border border-slate-200/80 rounded-xl p-3 flex items-center justify-between gap-3 shadow-sm";

        row.innerHTML = `
            <div class="flex items-center gap-3">
                <input type="checkbox" ${item.checked ? 'checked' : ''} onchange="toggleCartCheck('${item.cartId}')" class="custom-checkbox">
                <img src="${item.image}" alt="${item.name}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                <div class="flex flex-col">
                    <h4 class="font-bold text-slate-800 text-xs truncate max-w-[110px] sm:max-w-[130px]">${item.name}</h4>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">${item.specs}</span>
                    <div class="flex items-center gap-2 mt-1.5">
                        <button onclick="updateQty('${item.cartId}', -1)" class="w-5 h-5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition">-</button>
                        <span class="text-xs font-bold text-slate-700 min-w-[12px] text-center">${item.qty}</span>
                        <button onclick="updateQty('${item.cartId}', 1)" class="w-5 h-5 rounded-md orange-btn-gradient text-white font-bold text-xs flex items-center justify-center transition">+</button>
                    </div>
                </div>
            </div>
            <div class="flex flex-col items-end justify-between self-stretch">
                <button onclick="removeCartItem('${item.cartId}')" class="text-slate-300 hover:text-red-500 text-xs transition">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
                <div class="text-right">
                    <span class="text-xs font-extrabold text-slate-800">₱${itemTotal.toFixed(2)}</span>
                </div>
            </div>
        `;
        list.appendChild(row);
    });

    updateTotals();
}

function updateTotals() {
    const checkedItems = cartItems.filter(i => i.checked);
    const subtotal     = checkedItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const discount     = subtotal * discountRate;
    const total        = subtotal - discount;

    document.getElementById('subtotalPrice').innerText     = `₱${subtotal.toFixed(2)}`;
    document.getElementById('discountPrice').innerText     = `-₱${discount.toFixed(2)}`;
    document.getElementById('totalPaymentPrice').innerText = `₱${total.toFixed(2)}`;
    document.getElementById('discountPercentText').innerText = `${Math.round(discountRate * 100)}%`;

    const discountRow = document.getElementById('discountRow');
    if (discountRate > 0 && discount > 0) discountRow.classList.remove('hidden');
    else discountRow.classList.add('hidden');
}

/* ---------- Voucher (PHP) ---------- */
async function applyVoucher() {
    const statusDiv = document.getElementById('voucherStatus');
    const code      = document.getElementById('voucherInput').value.trim().toUpperCase();

    if (!code) {
        statusDiv.className = 'text-[11px] font-semibold text-red-500';
        statusDiv.innerText = 'Please enter a voucher code.';
        statusDiv.classList.remove('hidden');
        return;
    }

    const fd = new FormData();
    fd.append('code', code);

    try {
        const res  = await fetch('api/voucher.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.valid) {
            discountRate   = data.discount_rate;
            appliedVoucher = data.code;
            statusDiv.className = 'text-[11px] font-semibold text-emerald-600';
            statusDiv.innerText = data.message;
            showToast(`Voucher ${data.code} Applied!`);
        } else {
            discountRate   = 0;
            appliedVoucher = null;
            statusDiv.className = 'text-[11px] font-semibold text-red-500';
            statusDiv.innerText = data.message;
        }
        statusDiv.classList.remove('hidden');
        updateTotals();
    } catch (err) {
        statusDiv.className = 'text-[11px] font-semibold text-red-500';
        statusDiv.innerText = 'Error contacting server.';
        statusDiv.classList.remove('hidden');
    }
}

/* ---------- Cart mutations ---------- */
function toggleCartCheck(cartId) {
    const item = cartItems.find(i => i.cartId === cartId);
    if (item) { item.checked = !item.checked; updateTotals(); }
}

function updateQty(cartId, delta) {
    const item = cartItems.find(i => i.cartId === cartId);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) removeCartItem(cartId);
    else renderCart();
}

function removeCartItem(cartId) {
    cartItems = cartItems.filter(i => i.cartId !== cartId);
    renderCart();
    showToast('Item removed from cart');
}

function clearAllCart() {
    if (cartItems.length === 0) return;
    cartItems = [];
    renderCart();
    showToast('Cart cleared');
}

function addToCartFromGrid(prodId) {
    const prod = products.find(p => p.id === prodId);
    if (!prod) return;

    const existing = cartItems.find(i => i.prodId === prodId);
    if (existing) {
        existing.qty += 1;
        existing.checked = true;
    } else {
        cartItems.push({
            cartId: 'cart-' + Date.now() + '-' + prodId,
            prodId: prod.id,
            name:   prod.name,
            specs:  prod.specs,
            price:  prod.price,
            qty:    1,
            checked:true,
            image:  prod.image
        });
    }
    renderCart();
    showToast(`Added ${prod.name}`);
}

function toggleLike(prodId) {
    const prod = products.find(p => p.id === prodId);
    if (prod) { prod.liked = !prod.liked; renderProducts(); }
}

/* ---------- Filters ---------- */
function filterProducts() {
    const query    = document.getElementById('searchInput').value.toLowerCase();
    const filtered = products.filter(p =>
        p.name.toLowerCase().includes(query) ||
        (p.specs || '').toLowerCase().includes(query)
    );
    renderProducts(filtered);
}

function filterCategory(categoryName, btn) {
    activeCategory = categoryName;
    document.querySelectorAll('.category-pill').forEach(b => {
        b.classList.remove('bg-slate-900', 'text-white');
        b.classList.add('bg-slate-100', 'text-slate-600');
    });
    btn.classList.remove('bg-slate-100', 'text-slate-600');
    btn.classList.add('bg-slate-900', 'text-white');
    renderProducts();
}

/* ---------- Toast ---------- */
function showToast(msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMessage').innerText = msg;
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 2200);
}

/* ---------- Checkout (PHP) ---------- */
async function proceedToPayment() {
    const checkedItems = cartItems.filter(i => i.checked);
    if (checkedItems.length === 0) {
        showToast('Select an item to proceed');
        return;
    }

    const payload = {
        user_id: 1,
        voucher: appliedVoucher,
        items:   checkedItems.map(i => ({ product_id: i.prodId, quantity: i.qty }))
    };

    try {
        const res  = await fetch('api/order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (!data.success) {
            showToast(data.error || 'Order failed');
            return;
        }

        const ord = data.order;
        const discount = parseFloat(ord.discount_amount);

        document.getElementById('orderModalSummary').innerHTML = `
            <div class="flex justify-between text-slate-600"><span>Order Code:</span> <span class="font-bold text-slate-800">${ord.order_code}</span></div>
            <div class="flex justify-between text-slate-600"><span>Selected Items:</span> <span class="font-bold text-slate-800">${checkedItems.length}</span></div>
            <div class="flex justify-between text-slate-600"><span>Subtotal:</span> <span class="font-bold text-slate-800">₱${parseFloat(ord.subtotal).toFixed(2)}</span></div>
            ${discount > 0 ? `<div class="flex justify-between text-slate-600"><span>Voucher Discount:</span> <span class="font-bold text-emerald-600">-₱${discount.toFixed(2)}</span></div>` : ''}
            <div class="h-px bg-slate-200 my-1"></div>
            <div class="flex justify-between text-slate-800 font-bold text-sm"><span>Total Paid:</span> <span class="text-orange-600">₱${parseFloat(ord.total_amount).toFixed(2)}</span></div>
        `;

        const modal = document.getElementById('paymentModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
        }, 10);

        // Reset cart & refresh stock
        cartItems = [];
        renderCart();
        loadProducts();
    } catch (err) {
        showToast('Server error: ' + err.message);
    }
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.classList.add('opacity-0');
    modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

/* ---------- Init ---------- */
window.onload = function() {
    loadProducts();
    renderCart();
};