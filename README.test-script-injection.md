# Script Injection Test Document

This file includes a number of script tags in HTML that your document reader should make safe.  If any of these scripts
run (you'll know if they do as they all show an alert box) please contact the author and let him know.


<script>alert('Script A');</script>

</pre><script>alert('Script B');</script>

```<script>alert('Script C');</script>```

    <script>alert('Script D');</script>

Please <a onclick="alert('Script E')">click me</a>

    </code><script>alert('Script F');</script><code>
