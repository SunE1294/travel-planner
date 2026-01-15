<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghurte Jai | AI ট্রাভেল প্ল্যানার</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Hind Siliguri', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .loader { border-top-color: #FACC15; animation: spinner 1.5s linear infinite; }
        @keyframes spinner { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        /* এআই রেসপন্স সুন্দর দেখানোর জন্য কিছু CSS */
        #aiResponse h3 { font-size: 1.5rem; font-weight: bold; color: #1e40af; margin-top: 1rem; }
        #aiResponse ul { list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem; }
        #aiResponse p { margin-bottom: 0.5rem; line-height: 1.6; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <header class="bg-blue-600 text-white py-12 px-4 shadow-lg text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-2">ঘুরতে যাই <i class="fas fa-route text-yellow-400"></i></h1>
        <p class="text-lg opacity-90">আপনার বাজেট এবং মাস অনুযায়ী AI খুঁজে দিবে সেরা গন্তব্য</p>
    </header>

    <main class="container mx-auto px-4 -mt-10">
        <div class="glass-effect rounded-2xl shadow-xl p-6 md:p-8 max-w-5xl mx-auto border border-white">
            <form id="travelForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 italic">কোথায় যাবেন?</label>
                    <input type="text" id="location" placeholder="যেমন: বান্দরবান" class="w-full border rounded-lg p-3 outline-none focus:ring-2 focus:ring-blue-400 transition" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 italic">ভ্রমণের মাস</label>
                    <select id="month" class="w-full border rounded-lg p-3 outline-none focus:ring-2 focus:ring-blue-400">
                        <option>জানুয়ারি</option><option>ফেব্রুয়ারি</option><option>মার্চ</option>
                        <option>এপ্রিল</option><option>মে</option><option>জুন</option>
                        <option>জুলাই</option><option>আগস্ট</option><option>সেপ্টেম্বর</option>
                        <option>অক্টোবর</option><option>নভেম্বর</option><option>ডিসেম্বর</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 italic">বাজেট (টাকা)</label>
                    <input type="number" id="budget" placeholder="যেমন: ৫০০০" class="w-full border rounded-lg p-3 outline-none focus:ring-2 focus:ring-blue-400" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 italic">কতদিন থাকবেন?</label>
                    <input type="number" id="days" placeholder="যেমন: ৩" class="w-full border rounded-lg p-3 outline-none focus:ring-2 focus:ring-blue-400" required>
                </div>
                <div class="lg:col-span-4 mt-2">
                    <button type="submit" id="submitBtn" class="w-full bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-4 rounded-xl text-lg shadow-md transition-all flex justify-center items-center">
                        <span id="btnText">প্ল্যান দেখান</span>
                        <div id="btnLoader" class="hidden loader w-6 h-6 border-4 border-white rounded-full ml-3"></div>
                    </button>
                </div>
            </form>
        </div>

        <div id="resultArea" class="hidden max-w-5xl mx-auto my-10 space-y-6">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border-t-8 border-blue-500 p-6 md:p-8">
                <div id="aiResponse" class="prose max-w-none text-gray-800">
                    </div>
                
                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-between items-center">
                    <button onclick="window.print()" class="text-blue-600 font-semibold hover:underline">
                        <i class="fas fa-download mr-1"></i> পিডিএফ সেভ করুন
                    </button>
                    <div class="flex space-x-3 text-gray-400">
                        <i class="fab fa-facebook hover:text-blue-600 cursor-pointer"></i>
                        <i class="fab fa-whatsapp hover:text-green-500 cursor-pointer"></i>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('travelForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoader = document.getElementById('btnLoader');
            const resultArea = document.getElementById('resultArea');
            const aiResponse = document.getElementById('aiResponse');

            const data = {
                location: document.getElementById('location').value,
                month: document.getElementById('month').value,
                budget: document.getElementById('budget').value,
                days: document.getElementById('days').value
            };

            btnText.innerText = "AI প্ল্যান বানাচ্ছে...";
            btnLoader.classList.remove('hidden');
            submitBtn.disabled = true;
            resultArea.classList.add('hidden');

            try {
                const response = await fetch('/api/generate-plan', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        // লারাভেলের জন্য এই হেডারটি দেওয়া ভালো
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (response.ok) {
                    aiResponse.innerHTML = result.plan; 
                    resultArea.classList.remove('hidden');
                    // স্ক্রল করে রেজাল্টে নিয়ে যাবে
                    resultArea.scrollIntoView({ behavior: 'smooth' });
                } else {
                    // সার্ভার থেকে আসা নির্দিষ্ট এরর মেসেজ দেখাবে
                    throw new Error(result.plan || "সার্ভার থেকে সঠিক উত্তর পাওয়া যায়নি।");
                }
                
            } catch (error) {
                console.error("Error Details:", error);
                aiResponse.innerHTML = `
                    <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                        <p class="text-red-700 font-bold">সমস্যা হয়েছে!</p>
                        <p class="text-red-600 text-sm">${error.message}</p>
                        <p class="text-gray-500 text-xs mt-2">টিপস: টার্মিনালে php artisan serve চালু আছে কি না এবং .env ফাইলে API Key ঠিক আছে কি না দেখুন।</p>
                    </div>
                `;
                resultArea.classList.remove('hidden');
            } finally {
                btnText.innerText = "প্ল্যান দেখান";
                btnLoader.classList.add('hidden');
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html><?php /**PATH C:\Users\sany3\travel-system\resources\views/welcome.blade.php ENDPATH**/ ?>