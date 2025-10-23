<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:200px; padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Vorname:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->adUser->firstname }}
            @if($mutation->vorname && $mutation->vorname !== $mutation->adUser->firstname)
                (Neu: {{ $mutation->vorname }})
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Nachname:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->adUser->lastname }}
            @if($mutation->nachname && $mutation->nachname !== $mutation->adUser->lastname)
                (Neu: {{ $mutation->nachname }})
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Arbeitsort:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->adUser->arbeitsort?->name }}
            @if($mutation->arbeitsort && $mutation->arbeitsort->name !== $mutation->adUser->arbeitsort?->name)
                (Neu: {{ $mutation->arbeitsort->name }})
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Unternehmenseinheit:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->adUser->unternehmenseinheit?->name }}
            @if($mutation->unternehmenseinheit && $mutation->unternehmenseinheit->name !== $mutation->adUser->unternehmenseinheit?->name)
                (Neu: {{ $mutation->unternehmenseinheit->name }})
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Abteilung:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->adUser->abteilung?->name }}
            @if($mutation->abteilung && $mutation->abteilung->name !== $mutation->adUser->abteilung?->name)
                (Neu: {{ $mutation->abteilung->name }})
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Funktion:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->adUser->funktion?->name }}
            @if($mutation->funktion && $mutation->funktion->name !== $mutation->adUser->funktion?->name)
                (Neu: {{ $mutation->funktion->name }})
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Eintrittsdatum:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->vertragsbeginn?->format('d.m.Y') }}
        </td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Antragsteller:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->antragsteller?->display_name }}
        </td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Antragsteller E-Mail:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">
            {{ $mutation->antragsteller?->email }}
        </td>
    </tr>
</table>
