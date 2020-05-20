<h2 class="text-center my-4">
  <b class="featuresHeaderText">
    @if(app()->getLocale() == "en")
      What is it?
    @else
      ما هذا؟
    @endif
  </b>
</h2>

<div class="row whatIsIt__container" style="">
  <div class="col-sm-12" style="line-height: 2;">
    @if(app()->getLocale() == "en")
      Twitter is a great platform, but sometimes it feels out of control. TwUtils is a set of utilities and services to help you on that!
      <br>
      TwUtils will serve you if ...
      <ul style="">
        <li>You want to delete your tweets at once.</li>
        <li>You want to delete your likes (favorites) at once.</li>
        <li>You want to export/download your likes or tweets <small class="text-muted">(With or without it's attachments downloaded as ZIP File)</small>.</li>
        <li>You want to export/download your followings/followers.</li>
        <li>You want to quickly search for a user on your followings/followers list.</li>
      </ul>
    @else
      تويتر منصة رائعة، لكنها أحياناً تُصبِح خارجة عن السيطرة. تويتلز عبارة عن مجموعة من الخدمات لتُساعدَك في هذا الأمر!
      <br>
      تويتلز سيُصبحُ مفيداً لك لو أنكَ ...
      <ul style="">
        <li>
          ترغب بحذف جميع أو بعض تغريداتك مرة واحدة وإلى الأبد.
        </li>
        <li>
          ترغب بحذف جميع أو بعض مفضّلتك مرة واحدة وإلى الأبد.
        </li>
        <li>
          ترغب بالاحتفاظ أو تحميل وتصدير جميع تغريداتك أو مفضّلتك <small class="text-muted">(مع أو بدون ملفات الوسائط المرفقة تُحمّل كملف ZIP)</small>.
        </li>
        <li>
          ترغب بالاحتفاظ أو تحميل وتصدير جميع قائمة مُتابِعينك أو المُتابَعين من قبلك.
        </li>
        <li>
          تريد أن تستعرض قائمة المُتابِعين أو المُتابَعين والبحث فيها بسرعة.
        </li>
      </ul>
    @endif
  </div>
</div>
<!-- /.row -->
