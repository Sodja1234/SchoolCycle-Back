<x-mail::message>
    # Vérification de votre adresse e-mail

    Bonjour {{ $user->name }},

    Merci de vous être inscrit sur notre plateforme.
    Pour finaliser votre inscription, veuillez vérifier votre adresse e-mail en cliquant sur le bouton ci-dessous :

    <x-mail::button :url="$url">
        Vérifier mon adresse e-mail
    </x-mail::button>

    Ce lien expirera dans 60 minutes.

    Si vous n’avez pas créé de compte, vous pouvez ignorer cet e-mail.

    Merci,<br>
    L’équipe {{ config('app.name') }}
</x-mail::message>