<?php
// experiment10/calculator.php
// Simple scientific calculator with safe server-side evaluation using shunting-yard + RPN

function tokenize($expr){
    $expr = trim($expr);
    $len = strlen($expr);
    $tokens = [];
    $i = 0;
    while($i < $len){
        $c = $expr[$i];
        if (ctype_space($c)){ $i++; continue; }
        if (ctype_digit($c) || $c === '.'){
            $num = '';
            while($i<$len && (ctype_digit($expr[$i]) || $expr[$i]=='.')){ $num .= $expr[$i]; $i++; }
            $tokens[] = ['type'=>'number','value'=>$num];
            continue;
        }
        // identifiers: functions or variables (we only allow known functions)
        if (ctype_alpha($c)){
            $id = '';
            while($i<$len && ctype_alpha($expr[$i])){ $id .= $expr[$i]; $i++; }
            $tokens[] = ['type'=>'ident','value'=>strtolower($id)];
            continue;
        }
        // operators and parentheses
        if (strpos('+-*/^(),', $c) !== false){
            $tokens[] = ['type'=>'op','value'=>$c];
            $i++; continue;
        }
        // unknown character
        throw new Exception('Invalid character: '. $c);
    }
    return $tokens;
}

function shuntingYard($tokens){
    $out = [];
    $stack = [];

    $precedence = ['+'=>2,'-'=>2,'*'=>3,'/'=>3,'^'=>4];
    $rightAssoc = ['^'=>true];

    foreach($tokens as $i => $t){
        if ($t['type'] === 'number'){
            $out[] = $t;
        } elseif ($t['type'] === 'ident'){
            // function
            $stack[] = $t;
        } elseif ($t['type'] === 'op'){
            $op = $t['value'];
            if ($op === ','){
                // function arg separator: pop until left parenthesis
                while(!empty($stack) && end($stack)['value'] !== '('){ $out[] = array_pop($stack); }
                if (empty($stack)) throw new Exception('Misplaced comma or parentheses');
            } elseif ($op === '('){
                $stack[] = $t;
            } elseif ($op === ')'){
                while(!empty($stack) && end($stack)['value'] !== '('){ $out[] = array_pop($stack); }
                if (empty($stack)) throw new Exception('Mismatched parentheses');
                array_pop($stack); // pop '('
                if (!empty($stack) && end($stack)['type'] === 'ident'){
                    $out[] = array_pop($stack); // function -> output
                }
            } else {
                // operator
                while(!empty($stack) && end($stack)['type']==='op' && end($stack)['value']!=='('){
                    $top = end($stack)['value'];
                    $precTop = $precedence[$top] ?? 0;
                    $precOp = $precedence[$op] ?? 0;
                    if (($rightAssoc[$op] ?? false) ? ($precOp < $precTop) : ($precOp <= $precTop)){
                        $out[] = array_pop($stack);
                        continue;
                    }
                    break;
                }
                $stack[] = $t;
            }
        }
    }

    while(!empty($stack)){
        $t = array_pop($stack);
        if ($t['value'] === '(' || $t['value'] === ')') throw new Exception('Mismatched parentheses');
        $out[] = $t;
    }
    return $out;
}

function evaluateRPN($rpn){
    $stack = [];
    $allowedFuncs = ['sin','cos','tan','sqrt','abs','log','exp','pow','max','min'];
    foreach($rpn as $t){
        if ($t['type'] === 'number'){
            $stack[] = (float)$t['value'];
        } elseif ($t['type'] === 'op'){
            $op = $t['value'];
            if ($op === '+'){ $b=array_pop($stack); $a=array_pop($stack); $stack[] = $a + $b; }
            elseif ($op === '-') { $b=array_pop($stack); $a=array_pop($stack); $stack[] = $a - $b; }
            elseif ($op === '*') { $b=array_pop($stack); $a=array_pop($stack); $stack[] = $a * $b; }
            elseif ($op === '/') { $b=array_pop($stack); $a=array_pop($stack); if ($b==0) throw new Exception('Division by zero'); $stack[] = $a / $b; }
            elseif ($op === '^') { $b=array_pop($stack); $a=array_pop($stack); $stack[] = pow($a,$b); }
            else throw new Exception('Unknown operator: '.$op);
        } elseif ($t['type'] === 'ident'){
            $fn = $t['value'];
            if (!in_array($fn, $allowedFuncs)) throw new Exception('Unknown function: '.$fn);
            if ($fn === 'pow'){
                $b = array_pop($stack); $a = array_pop($stack); $stack[] = pow($a,$b);
            } elseif (in_array($fn,['max','min'])){
                // support two args
                $b = array_pop($stack); $a = array_pop($stack); $stack[] = $fn($a,$b);
            } else {
                $a = array_pop($stack);
                switch($fn){
                    case 'sin': $stack[] = sin($a); break;
                    case 'cos': $stack[] = cos($a); break;
                    case 'tan': $stack[] = tan($a); break;
                    case 'sqrt': if ($a < 0) throw new Exception('sqrt of negative'); $stack[] = sqrt($a); break;
                    case 'abs': $stack[] = abs($a); break;
                    case 'log': if ($a <= 0) throw new Exception('log domain'); $stack[] = log($a); break;
                    case 'exp': $stack[] = exp($a); break;
                    default: throw new Exception('Unhandled function');
                }
            }
        }
    }
    if (count($stack) !== 1) throw new Exception('Invalid expression');
    return $stack[0];
}

function evaluateExpression($expr){
    // quick sanitize: allow digits, letters (for function names), operators, dot, parentheses, commas, and whitespace
    if (preg_match('/[^0-9a-zA-Z+\-\*\/\^\.(),\s]/', $expr)) throw new Exception('Invalid characters in expression');

    // convert unary minus into 0 - x when appropriate
    $expr = preg_replace('/(^|[\(,\+\-\*\/\^])\s*\-\s*/', '$1~', $expr);
    // replace unary marker ~ with (0-<expr>) during tokenization handling
    $expr = str_replace('~', '(0-', $expr);
    // ensure we close parentheses inserted by unary minus; we'll add closing ) after number/identifier or )
    // simpler approach: append a closing ) after any number/ident or ) that follows the unary -; to keep parsing simple, we won't attempt to auto-close but instead handle unary minus in tokenizer by emitting '0' and '-' tokens

    // Better: handle unary minus directly in tokenizer by pre-processing
    // For now, revert approach and handle unary minus in tokenizer below
    // Tokenize
    $tokens = tokenize($expr);

    // But our tokenizer does not treat ~ specially; if above replaced, it may cause issues. To avoid complexity, a robust approach is to reimplement unary handling:
    // Re-tokenize with unary handling: insert number 0 before unary minus
    $fixed = [];
    $prev = null;
    foreach($tokens as $t){
        if ($t['type']==='op' && $t['value']==='-'){
            if ($prev === null || ($prev['type']==='op' && $prev['value']!==')')){
                // unary minus -> convert to (0 - ... ) by pushing 0 then '-'
                $fixed[] = ['type'=>'number','value'=>'0'];
                $fixed[] = $t;
                $prev = $t; continue;
            }
        }
        $fixed[] = $t; $prev = $t;
    }

    $rpn = shuntingYard($fixed);
    return evaluateRPN($rpn);
}

$result = null; $error = null; $exprIn = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $exprIn = $_POST['expr'] ?? '';
    try{
        $result = evaluateExpression($exprIn);
    } catch (Exception $e){
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Experiment 10 — PHP Scientific Calculator</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;padding:20px;max-width:700px;margin:auto}
    form{display:flex;gap:8px;align-items:center;margin-bottom:12px}
    input[type=text]{flex:1;padding:8px;border:1px solid #ccc;border-radius:4px}
    button{padding:8px 12px;border-radius:4px}
    .result{padding:12px;background:#f6f9ff;border:1px solid #d6e0ff;border-radius:6px}
    .err{background:#fff0f0;border:1px solid #f3c2c2}
    .help{font-size:0.9rem;color:#555;margin-top:8px}
  </style>
</head>
<body>
  <h1>Experiment 10 — PHP Scientific Calculator</h1>
  <form method="post">
    <label for="expr" class="sr">Expression</label>
    <input id="expr" name="expr" type="text" placeholder="e.g. 2+3*4, sin(3.14/2), pow(2,3), sqrt(9)" value="<?= htmlspecialchars($exprIn) ?>">
    <button type="submit">Evaluate</button>
  </form>

  <?php if ($error !== null): ?>
    <div class="result err">Error: <?= htmlspecialchars($error) ?></div>
  <?php elseif ($result !== null): ?>
    <div class="result">Result: <?= htmlspecialchars((string)$result) ?></div>
  <?php else: ?>
    <div class="help">Enter a mathematical expression. Supported functions: sin, cos, tan, sqrt, abs, log, exp, pow, max, min. Use ^ for power.</div>
  <?php endif; ?>

  <hr>
  <p>Examples:</p>
  <ul>
    <li>2 + 3 * 4</li>
    <li>sin(3.14159 / 2)</li>
    <li>pow(2,3) or 2^3</li>
    <li>sqrt(16)</li>
  </ul>
</body>
</html>
