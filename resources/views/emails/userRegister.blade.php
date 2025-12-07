<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>【Conpass】新規登録申請を受け付けました</title>
</head>
<body>
<p>{{ $data['full_name'] }}様</p>

<p>このたびはConpassにご登録いただき、ありがとうございます。<br>
    新規登録の申請を受け付けました。</p>

<p>後日、担当コンシェルジュよりお電話にて確認のご連絡をさせていただきます。<br>
    お手数ですが、ご対応のほどよろしくお願いいたします。</p>

<p>お電話について</p>
<p>◾️ご連絡のタイミング：2営業日以内（平日9:00〜18:00）<br>
    ◾️確認内容：ご登録情報の確認とサービスのご案内</p>

<p>ご不明点がございましたら、お気軽にお問い合わせください。</p>

<p>-------------</p>

<p>お名前： {{$data['full_name']}}<br>
    勤務先企業名： {{$data['company_name']}}<br>
    メールアドレス： {{$data['email']}}<br>
    直通電話番号： {{$data['user_phone']}}<br>
    部署： {{$data['division']}}<br>
    役職： {{$data['position']}}<br>
    業種： {{$data['industry']}}<br>
    従業員規模： {{$data['employee_size']}}<br>
    知ったきっかけ： {{$data['how_found_us']}}</p>

<p>-------------</p>

<p>Conpassのサイトはこちら<br>
<p>[Conpass：<a href="{{ $data['url_site'] }}">{{ $data['url_site'] }}</a>]</p>

<p>このメールに心当たりがない方、ご不明点がある方はお問い合わせください。<br>
    お問い合わせ先: corporate@markelon.jp</p>

<br><br>
<p>------------------------------------------------------------</p>
<p>※こちらのメールは送信専用です。ご返信いただきましても対応は致しかねますのでご注意ください。</p>

<p>MarkeLon株式会社　Conpass運営チーム</p>
</body>
</html>
