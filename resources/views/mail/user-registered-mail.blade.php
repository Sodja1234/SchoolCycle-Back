<x-mail::message>
# 🎉 Vérifiez votre adresse e-mail

Bonjour **{{ $user->name }}**,

Merci pour votre inscription sur **{{ config('app.name') }}** !

---

## ✅ Procedure 1 : Vérification par lien (Web/Desktop)

Cliquez sur le bouton ci-dessous pour vérifier automatiquement votre adresse e-mail :

<x-mail::button :url="$url" color="primary">
Vérifier mon adresse e-mail
</x-mail::button>

Ce lien expirera dans **60 minutes**.

---

## 📱 Procedure 2 : Code OTP pour vérification mobile

Si vous utilisez un appareil mobile ou une application ne supportant pas les liens, vous pouvez entrer ce code OTP pour vérifier votre adresse e-mail :

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

Ce code expirera dans **10 minutes**.

---

Si vous n’avez pas créé de compte, vous pouvez ignorer cet e-mail.

Merci,  
L’équipe **{{ config('app.name') }}**
</x-mail::message>
