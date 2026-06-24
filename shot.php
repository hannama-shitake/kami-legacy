<?php
$env = file_get_contents("/home/achoo/.env_kami");
preg_match("/ANTHROPIC_API_KEY=(.+)/", $env, $m1);
preg_match("/WP_APP_PASSWORD=(.+)/", $env, $m2);
$claude_key = trim($m1[1] ?? "");
$wp_pass = trim($m2[1] ?? "");
$result = "";
$shrine_name = "";
$wp_saved = "";

function call_claude($key, $content) {
    $ch = curl_init("https://api.anthropic.com/v1/messages");
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode(["model"=>"claude-sonnet-4-6","max_tokens"=>3000,"messages"=>[["role"=>"user","content"=>$content]]]),CURLOPT_HTTPHEADER=>["Content-Type: application/json","x-api-key: ".$key,"anthropic-version: 2023-06-01"]]);
    $data = json_decode(curl_exec($ch),true);
    curl_close($ch);
    return $data["content"][0]["text"] ?? "失敗";
}

function save_to_wp($title, $content, $pass) {
    $ch = curl_init("https://kami-legacy.com/wp-json/wp/v2/posts");
    $auth = base64_encode("achoo:" . $pass);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode(["title"=>$title,"content"=>nl2br($content),"status"=>"draft"]),CURLOPT_HTTPHEADER=>["Content-Type: application/json","Authorization: Basic ".$auth]]);
    $data = json_decode(curl_exec($ch),true);
    curl_close($ch);
    return $data["link"] ?? ("エラー: " . json_encode($data));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "analyze";

    if ($action === "analyze") {
        $lat = $_POST["lat"] ?? "";
        $lng = $_POST["lng"] ?? "";
        $content = [];

        if (!empty($_FILES["image"]["tmp_name"])) {
            $content[] = ["type"=>"image","source"=>["type"=>"base64","media_type"=>$_FILES["image"]["type"],"data"=>base64_encode(file_get_contents($_FILES["image"]["tmp_name"]))]];
        }

        $geo = ($lat && $lng) ? "現在地GPS: 緯度{$lat} 経度{$lng}\n" : "";
        $prompt = $geo."【重要】まず最初の行に「神社名:〇〇神社」の形式で神社名だけを書け。\n\n以下の4項目を解析せよ。祭神が複数いる場合は全員の関係と役割を記せ。\n\n【陽の由緒】主祭神・相殿神を全て挙げ、それぞれの神格と歴史的役割を記せ。\n【陰の真実】戦乱・略奪・権力闘争・神仏習合と廃仏毀釈など史実の暗部を具体的に。\n【神格の矛盾】祭神の組み合わせに矛盾や政治的意図がある場合、その背景を読め。勝者の神と敗者の神が同居する理由、土着神が吸収・封印された経緯、怨霊鎮魂の構造など。矛盾がなければ省略してよい。\n【この地の因縁】訪れた者への言葉として。\n架空不要。史実と神話構造の重みをそのまま語れ。";
        $content[] = ["type"=>"text","text"=>$prompt];

        $raw = call_claude($claude_key, $content);

        if (preg_match('/神社名[:：]\s*(.+)/u', $raw, $nm)) {
            $shrine_name = trim($nm[1]);
        } else {
            $shrine_name = "神社";
        }
        $result = $raw;

    } elseif ($action === "save_wp") {
        $result = $_POST["result"] ?? "";
        $shrine_name = $_POST["shrine_name"] ?? "神社";
        $wp_saved = save_to_wp($shrine_name . " 御由緒解析", $result, $wp_pass);
    }
}
?><!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>KAMI-LEGACY</title><style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#0a0a0a;color:#e8e0d0;font-family:serif;padding:40px 20px}
.w{max-width:600px;margin:0 auto}
h1{text-align:center;color:#c9a84c;letter-spacing:.3em;margin-bottom:8px}
.s{text-align:center;color:#666;font-size:.8em;margin-bottom:40px}
.gps{text-align:center;color:#888;font-size:.75em;margin-bottom:24px;min-height:1.2em}
label{display:block;color:#c9a84c;font-size:.85em;margin-bottom:8px}
input[type=file]{width:100%;padding:12px;background:#111;border:1px solid #333;color:#e8e0d0;font-size:.9em;margin-bottom:20px}
button{width:100%;padding:15px;background:#c9a84c;color:#0a0a0a;border:none;font-size:1em;cursor:pointer;letter-spacing:.2em;margin-bottom:12px}
.btn-wp{background:#1e3a5f;color:#e8e0d0}
.shrine-title{color:#c9a84c;font-size:1.2em;letter-spacing:.2em;text-align:center;margin:24px 0 8px}
.r{border-top:1px solid #333;padding-top:24px;white-space:pre-wrap;line-height:2}
.saved{color:#4caf50;margin-top:16px;font-size:.9em}
</style></head><body><div class="w">
<h1>⛩ KAMI-LEGACY</h1>
<p class="s">神々の記憶を翻訳する場所</p>

<?php if(!$result):?>
<p class="gps" id="gps-status">📍 位置情報を取得中...</p>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="analyze">
<input type="hidden" name="lat" id="lat">
<input type="hidden" name="lng" id="lng">
<label>📷 御由緒書きを撮影</label>
<input type="file" name="image" accept="image/*" capture="environment">
<button type="submit">解析する</button>
</form>
<script>
if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(function(pos){
        document.getElementById("lat").value=pos.coords.latitude.toFixed(6);
        document.getElementById("lng").value=pos.coords.longitude.toFixed(6);
        document.getElementById("gps-status").textContent="📍 "+pos.coords.latitude.toFixed(4)+","+pos.coords.longitude.toFixed(4);
    },function(){
        document.getElementById("gps-status").textContent="📍 位置情報なし";
    });
}else{
    document.getElementById("gps-status").textContent="📍 GPS非対応";
}
</script>

<?php else:?>
<?php if($shrine_name && $shrine_name !== "神社"):?>
<p class="shrine-title">⛩ <?=htmlspecialchars($shrine_name)?></p>
<?php endif;?>
<div class="r"><?=nl2br(htmlspecialchars($result))?></div>
<form method="POST" style="margin-top:24px">
<input type="hidden" name="action" value="save_wp">
<input type="hidden" name="shrine_name" value="<?=htmlspecialchars($shrine_name)?>">
<input type="hidden" name="result" value="<?=htmlspecialchars($result)?>">
<button type="submit" class="btn-wp">📝 WordPressに下書き保存</button>
</form>
<?php if($wp_saved):?><p class="saved">✅ 保存: <a href="<?=htmlspecialchars($wp_saved)?>" style="color:#c9a84c"><?=htmlspecialchars($wp_saved)?></a></p><?php endif;?>
<?php endif;?>

</div></body></html>
