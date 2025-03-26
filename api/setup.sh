#!/bin/bash

# AI News Scraper Auto Blogger Pro - API सेटअप स्क्रिप्ट
echo "AI News Scraper Auto Blogger Pro - API सेटअप शुरू हो रहा है..."

# Python के वर्जन की जांच करें
python_version=$(python3 --version 2>&1 | awk '{print $2}')
if [[ -z "$python_version" ]]; then
    echo "एरर: Python 3 इंस्टॉल नहीं है। कृपया Python 3.10 या उससे उच्चतर संस्करण इंस्टॉल करें।"
    exit 1
fi

echo "Python वर्जन पाया गया: $python_version"

# आवश्यक पैकेज इंस्टॉल करें
echo "आवश्यक Python पैकेज इंस्टॉल किए जा रहे हैं..."
pip install -r requirements.txt

# सेटअप पूरा
echo "API सेटअप पूरा हुआ!"
echo ""
echo "API सर्वर शुरू करने के लिए:"
echo "python main.py"
echo ""
echo "या उत्पादन वातावरण के लिए:"
echo "gunicorn --bind 0.0.0.0:5000 --workers 4 main:app"
echo ""
echo "WordPress प्लगइन सेटिंग्स में API URL सेट करना न भूलें:"
echo "http://your-server-ip:5000"