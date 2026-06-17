<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculator</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #121212;
            margin: 0;
            color: #fff;
        }

        .calculator {
            background-color: #1e1e1e;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 320px;
        }

        .display {
            background-color: #2c2c2c;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: right;
            font-size: 2em;
            word-wrap: break-word;
            min-height: 48px;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        button {
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 1.5em;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            display: flex;
            justify-content: center;
            align-items: center;
            user-select: none;
        }

        button:hover {
            background-color: #444;
        }

        button:active {
            transform: scale(0.95);
            background-color: #555;
        }

        button.operator {
            background-color: #ff9500;
            color: white;
        }

        button.operator:hover {
            background-color: #ffaa33;
        }

        button.clear {
            background-color: #a5a5a5;
            color: black;
        }

        button.clear:hover {
            background-color: #d4d4d2;
        }
        
        button.zero {
            grid-column: span 2;
            width: 135px;
            border-radius: 30px;
            justify-content: flex-start;
            padding-left: 25px;
        }
    </style>
</head>
<body>

<div class="calculator">
    <div class="display" id="display">0</div>
    <div class="buttons">
        <button class="clear" onclick="clearDisplay()">AC</button>
        <button class="clear" onclick="deleteLast()">DEL</button>
        <button class="operator" onclick="appendOperator('%')">%</button>
        <button class="operator" onclick="appendOperator('/')">÷</button>

        <button onclick="appendNumber('7')">7</button>
        <button onclick="appendNumber('8')">8</button>
        <button onclick="appendNumber('9')">9</button>
        <button class="operator" onclick="appendOperator('*')">×</button>

        <button onclick="appendNumber('4')">4</button>
        <button onclick="appendNumber('5')">5</button>
        <button onclick="appendNumber('6')">6</button>
        <button class="operator" onclick="appendOperator('-')">−</button>

        <button onclick="appendNumber('1')">1</button>
        <button onclick="appendNumber('2')">2</button>
        <button onclick="appendNumber('3')">3</button>
        <button class="operator" onclick="appendOperator('+')">+</button>

        <button class="zero" onclick="appendNumber('0')">0</button>
        <button onclick="appendNumber('.')">.</button>
        <button class="operator" onclick="calculate()">=</button>
    </div>
</div>

<script>
    let display = document.getElementById('display');
    let currentInput = '0';
    let justCalculated = false;

    function updateDisplay() {
        // Just for display purposes, replace * and / with nice symbols
        let displayText = currentInput.replace(/\*/g, '×').replace(/\//g, '÷');
        display.innerText = displayText;
    }

    function appendNumber(number) {
        if (currentInput === '0' || currentInput === 'Error' || justCalculated) {
            currentInput = number;
            justCalculated = false;
        } else {
            currentInput += number;
        }
        updateDisplay();
    }

    function appendOperator(operator) {
        if (currentInput === 'Error') {
            currentInput = '0';
        }
        
        let lastChar = currentInput.slice(-1);
        if (['+', '-', '*', '/', '%'].includes(lastChar)) {
            currentInput = currentInput.slice(0, -1) + operator;
        } else {
            currentInput += operator;
        }
        justCalculated = false;
        updateDisplay();
    }

    function clearDisplay() {
        currentInput = '0';
        justCalculated = false;
        updateDisplay();
    }

    function deleteLast() {
        if (currentInput === 'Error' || justCalculated) {
            currentInput = '0';
            justCalculated = false;
        } else if (currentInput.length > 1) {
            currentInput = currentInput.slice(0, -1);
        } else {
            currentInput = '0';
        }
        updateDisplay();
    }

    function calculate() {
        if (currentInput === 'Error') return;
        
        try {
            let result = eval(currentInput);
            
            if (!isFinite(result) || isNaN(result)) {
                currentInput = 'Error';
            } else {
                currentInput = String(Math.round(result * 100000000) / 100000000);
            }
        } catch (e) {
            currentInput = 'Error';
        }
        updateDisplay();
        justCalculated = true;
    }
</script>

</body>
</html>
