@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .wheel-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }

        .wheel {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
            transition: transform 4s ease-out;
            background: conic-gradient(#ff6b6b 0deg 31deg,
                    #4ecdc4 31deg 51deg,
                    #45b7d1 51deg 71deg,
                    #96c93d 71deg 91deg,
                    #f7d794 91deg 100deg);
        }

        .wheel spinning {
            transform: rotate(3600deg);
        }

        .wheel-section {
            position: absolute;
            width: 50%;
            height: 50%;
            top: 0;
            left: 50%;
            transform-origin: 0 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pointer {
            width: 20px;
            height: 40px;
            background: red;
            position: absolute;
            top: -40px;
            clip-path: polygon(50% 100%, 0 0, 100% 0);
            z-index: 10;
        }

        .spin-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .spin-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .result {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
@endpush
@php
    $title = 'Vòng Quay May Mắn';
@endphp
@section('content')
    <div class="wheel-container">
        <h2>Vòng Quay May Mắn</h2>
        <div id="wheel" class="wheel">
            <div class="pointer"></div>
            <!-- Các phần thưởng sẽ được thêm bằng JS -->
        </div>
        <button id="spinButton" class="spin-button">Quay</button>
        <div id="result" class="result"></div>
        <div id="spinsLeft">Số lượt quay còn lại: <span id="spinCount">0</span></div>
    </div>
@endsection
@push('page-scripts')
    <script>
        async function initializeCsrf() {
            await fetch('http://127.0.0.1:8000/sanctum/csrf-cookie', {
                method: 'GET',
                credentials: 'include'
            });
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.error('CSRF token not found. Please ensure <meta name="csrf-token"> is present in the HTML.');
            throw new Error('CSRF token not found');
        }
        // Lấy danh sách phần thưởng
        async function loadRewards() {
            const response = await fetch('http://127.0.0.1:8000/api/spins/rewards', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include' // Thêm dòng này
            });
            return await response.json();
        }

        // Lấy số lượt quay còn lại
        async function loadSpinCount() {
            const response = await fetch('http://127.0.0.1:8000/api/spins/user/spins', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include' // Thêm dòng này
            });
            const data = await response.json();
            document.getElementById('spinCount').textContent = data['Số lượt quay còn lại'];
        }

        // Vẽ vòng quay
        async function drawWheel() {
            const rewards = await loadRewards();
            const wheel = document.getElementById('wheel');
            const anglePerSection = 360 / rewards.length;

            rewards.forEach((reward, index) => {
                const section = document.createElement('div');
                section.className = 'wheel-section';
                section.style.transform = `rotate(${anglePerSection * index}deg)`;
                section.textContent = reward.name;
                wheel.appendChild(section);
            });

            return rewards;
        }

        // Xử lý quay
        async function spinWheel() {
            const spinButton = document.getElementById('spinButton');
            const wheel = document.getElementById('wheel');
            const result = document.getElementById('result');

            spinButton.disabled = true;
            result.textContent = 'Đang quay...';

            // Gọi API spin
            const response = await fetch('http://127.0.0.1:8000/api/spins/spin', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include' // Thêm dòng này
            });

            const data = await response.json();

            if (!response.ok) {
                result.textContent = data.message;
                spinButton.disabled = false;
                return;
            }

            const rewards = await loadRewards();
            const rewardIndex = rewards.findIndex(r => r.name === data.reward);
            const anglePerSection = 360 / rewards.length;
            const targetAngle = 3600 + (anglePerSection * rewardIndex) + (anglePerSection / 2);

            wheel.style.transition = 'none';
            wheel.style.transform = 'rotate(0deg)';

            setTimeout(() => {
                wheel.style.transition = 'transform 4s ease-out';
                wheel.style.transform = `rotate(${targetAngle}deg)`;
            }, 100);

            setTimeout(async () => {
                result.textContent = `Chúc mừng! Bạn nhận được: ${data.reward}` +
                    (data.coupon_code ? ` (Mã: ${data.coupon_code})` : '');
                spinButton.disabled = false;
                await loadSpinCount();
            }, 4100);
        }

        // Khởi tạo
        document.addEventListener('DOMContentLoaded', async () => {
            await initializeCsrf();
            await drawWheel();
            await loadSpinCount();

            document.getElementById('spinButton').addEventListener('click', spinWheel);
        });
    </script>
@endpush
