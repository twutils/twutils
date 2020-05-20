<!-- Portfolio Section -->
<h2 class="text-center my-4">
  <b class="featuresHeaderText">
    @if(app()->getLocale() == "en")
      How does it work?
    @else
      كيف يعمل؟
    @endif
  </b>
</h2>

<div class="row howStepsContainer">
  <div class="col-sm-12">
    <div class="stepBlock">
      <div class="circledStepNumber">
        <div class="circledStepNumberWrapper">
          <span class="circledStepNumberText">1</span>
        </div>
      </div>
      <div class="stepText">
        @if(app()->getLocale() == "en")
          @guest
          Link your Twitter Account.
          <a href="{{route('login')}}">Sign in with Twitter</a>.
          After logging in, you will have access to your Dashboard.
          @else
          Since you linked your account already, Navigate to 
          <a href="{{route('app')}}">the Dashboard</a>.
          @endauth
        @else
          @guest
          قم بربط حسابك على تويتر
          <a href="{{route('login')}}">سجّل دخول عبر تويتر</a>.
          بعد تسجيل الدخول، ستتمكّن من الوصول إلى لوحة التحكُّم
          @else
          لقد قُمتَ مُسبقاً بربط حسابك بتويتر! توجَّه إلى
          <a href="{{route('app')}}">لوحة التحكُّم</a> للبدء في استخدام تويتلز.
          @endauth
        @endif
      </div>
    </div>
    <div class="stepBlock">
      <div class="circledStepNumber">
        <div class="circledStepNumberWrapper">
          <span class="circledStepNumberText">2</span>
        </div>
      </div>
      <div class="stepText">
        @if(app()->getLocale() == "en")
          From the Dashboard, chose the task you want to do with your linked Twitter account. Currently the available tasks:
          <br>
          <ul class="my-3" style="line-height: 1.9;">
            <li>Backup Likes</li>
            <li>Backup Tweets</li>
            <li>Backup Followings</li>
            <li>Backup Followers</li>
            <li>Remove Tweets
              <small class="text-muted">
                Requires higher level access to your account.
              </small>
            </li>
            <li>Remove Likes
              <small class="text-muted">
                Requires higher level access to your account.
              </small>
            </li>
          </ul>
        @else
          من لوحة التحكُّم، اختر المهمة التي ترغب بالقيام بها لحساب تويتر الخاص بك، حالياً المهام المتوفِّرة:
          <br>
          <ul class="my-3" style="line-height: 1.9;">
            <li>نسخ المفضّلة</li>
            <li>نسخ التغريدات</li>
            <li>نسخ قائمة المُتابِعين</li>
            <li>نسخ قائمة المُتَابَعين</li>
            <li>حذف التغريدات
              <small class="text-muted">
                تتطلَّب صلاحيّات أعلى على حسابك في تويتر
              </small>
            </li>
            <li>حذف المفضّلة
              <small class="text-muted">
                تتطلَّب صلاحيّات أعلى على حسابك في تويتر
              </small>
            </li>
          </ul>
        @endif
      </div>
    </div>
    <div class="stepBlock">
      <div class="circledStepNumber">
        <div class="circledStepNumberWrapper">
          <span class="circledStepNumberText">3</span>
        </div>
      </div>
      <div class="stepText">
        @if(app()->getLocale() == "en")
          Your task will be on queue, processing, until it's completed. Done! It's now stored on TwUtils.
          <small class="text-muted d-block">You have a limited number of tasks to keep it stored on TwUtils, otherwise you will have to remove it.</small>
        @else
          ستُصبِح المهمة التي قُمتَ بإنشائها في الصف الخاص بالمهمات التي ستعمل في الخلفية، إلى أن تنتهي! عند ذلك ستُصبِح محفوظة في تويتلز
          <small class="text-muted d-block">لديك عدد محدود من المهام الممكن الاحتفاظ بها على تويتلز، لكن يمكنك حذف أي مهمة في أي وقت.</small>
        @endif
      </div>
    </div>
    <div class="stepBlock">
      <div class="circledStepNumber">
        <div class="circledStepNumberWrapper">
          <span class="circledStepNumberText">4</span>
        </div>
      </div>
      <div class="stepText">
        @if(app()->getLocale() == "en")
          It's your choice now if you want to leave it on TwUtils and get back to it later, or do something with it then remove it completely.
        @else
          الآن سيُصبح الخيار خيارك فيما لو ترغب في ترك المهمة محفوظة في تويتلز للعودة إليها لاحقاً، أو القيام بما ترغب به ثم حذفها من تويتلز بالكامل.
        @endif
      </div>
    </div>
  </div>
</div>
<!-- /.row -->
