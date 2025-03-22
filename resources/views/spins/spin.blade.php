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
            width: 500px;
            /* Tăng kích thước vòng quay */
            height: 500px;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
            /* Chia vòng quay thành 8 phần, mỗi phần 45 độ (360 / 8 = 45) */
            background: conic-gradient(#ff6b6b 0deg 45deg,
                    /* Ô 1: Đỏ */
                    #4ecdc4 45deg 90deg,
                    /* Ô 2: Xanh lam nhạt */
                    #45b7d1 90deg 135deg,
                    /* Ô 3: Xanh lam */
                    #96c93d 135deg 180deg,
                    /* Ô 4: Xanh lá */
                    #f7d794 180deg 225deg,
                    /* Ô 5: Vàng nhạt */
                    #ff9f43 225deg 270deg,
                    /* Ô 6: Cam */
                    #6ab04c 270deg 315deg,
                    /* Ô 7: Xanh lá đậm */
                    #a29bfe 315deg 360deg
                    /* Ô 8: Tím */
                );
            border: 5px solid #333;
            /* Thêm viền để đẹp hơn */
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
            font-size: 20px;
            /* Tăng kích thước chữ */
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            /* Thêm bóng chữ để dễ đọc */
        }

        .wheel-section span {
            display: block;
            transform: rotate(90deg);
            /* Xoay chữ theo góc nhìn */
            width: 100%;
            text-align: center;
        }

        .pointer {
            width: 30px;
            /* Tăng kích thước kim */
            height: 50px;
            background: red;
            position: absolute;
            top: -10px;
            /* Đặt kim ở vị trí 12 giờ */
            left: 50%;
            transform: translateX(-50%);
            clip-path: polygon(50% 100%, 0 0, 100% 0);
            z-index: 10;
            border: 2px solid #fff;
            /* Thêm viền trắng cho kim */
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
            transition: background-color 0.3s;
        }

        .spin-button:hover {
            background-color: #45a049;
        }

        .spin-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .result {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
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
        </div>
        <button id="spinButton" class="spin-button">Quay</button>
        <div id="result" class="result"></div>
        <div id="spinsLeft">Số lượt quay còn lại: <span id="spinCount">0</span></div>
    </div>
@endsection
@push('page-scripts')
    <script>
        // Khai báo csrfToken
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.error('CSRF token not found. Please ensure <meta name="csrf-token"> is present in the HTML.');
            throw new Error('CSRF token not found');
        }

        const baseUrl = 'http://127.0.0.1:8000';

        // Lấy CSRF cookie
        async function initializeCsrf() {
            const response = await fetch(`${baseUrl}/sanctum/csrf-cookie`, {
                method: 'GET',
                credentials: 'include'
            });
            console.log('CSRF cookie response:', response.status);
        }

        // Lấy danh sách phần thưởng
        async function loadRewards() {
            const response = await fetch(`${baseUrl}/api/spins/rewards`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            });
            if (!response.ok) {
                const errorData = await response.json();
                console.error('Load rewards error:', errorData);
                throw new Error('Không thể lấy danh sách phần thưởng');
            }
            return await response.json();
        }

        // Lấy số lượt quay còn lại
        async function loadSpinCount() {
            const response = await fetch(`${baseUrl}/api/spins/user/turn`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            });
            if (!response.ok) {
                const errorData = await response.json();
                console.error('Load spin count error:', errorData);
                throw new Error('Không thể lấy số lượt quay');
            }
            const data = await response.json();
            document.getElementById('spinCount').textContent = data['Số lượt quay còn lại'];
        }

        // Vẽ vòng quay
        async function drawWheel() {
            const rewards = await loadRewards();
            const wheel = document.getElementById('wheel');
            const anglePerSection = 360 / rewards.length; // 360 / 8 = 45 độ

            rewards.forEach((reward, index) => {
                const section = document.createElement('div');
                section.className = 'wheel-section';
                section.style.transform =
                    `rotate(${anglePerSection * index}deg) skewY(-${90 - anglePerSection}deg)`;
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

            // Gọi API để lấy kết quả quay
            const response = await fetch(`${baseUrl}/api/spins/spin`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            });

            const data = await response.json();
            if (!response.ok) {
                result.textContent = data.message;
                spinButton.disabled = false;
                return;
            }

            const rewards = await loadRewards(); // Lấy danh sách phần thưởng
            const rewardIndex = rewards.findIndex(r => r.name === data.reward); // Xác định phần quà trúng
            const totalSections = rewards.length;
            const anglePerSection = 360 / totalSections; // Mỗi phần bao nhiêu độ

            // 🎯 Tính toán góc quay chính xác
            const randomOffset = Math.random() * (anglePerSection - 5) + 5; // Tạo hiệu ứng ngẫu nhiên
            const targetAngle = 3600 + (anglePerSection * rewardIndex) + randomOffset;

            // Đặt trạng thái vòng quay về 0 trước khi quay
            wheel.style.transition = 'none';
            wheel.style.transform = 'rotate(0deg)';

            setTimeout(() => {
                wheel.style.transition = 'transform 4s ease-out';
                wheel.style.transform = `rotate(${targetAngle}deg)`;
            }, 100);

            setTimeout(async () => {
                result.textContent = `Chúc mừng! Bạn nhận được: ${data.reward}`;
                spinButton.disabled = false;
            }, 4100);
        }


        // Khởi tạo
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                await initializeCsrf();
                await drawWheel();
                await loadSpinCount();

                document.getElementById('spinButton').addEventListener('click', spinWheel);
            } catch (error) {
                console.error('Lỗi khởi tạo:', error);
                document.getElementById('result').textContent = 'Có lỗi xảy ra, vui lòng thử lại sau!';
            }
        });
    </script>
@endpush
