<tr>
    <td class="header">
        <a href="{{ $url }}">
            {{ Illuminate\Mail\Markdown::parse($slot) ?? '' }}
        </a>
    </td>
</tr>
