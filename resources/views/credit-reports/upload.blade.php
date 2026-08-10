@extends('layouts.app')

@section('title', 'Get Your Free Personalized Game Plan')

@section('content')
<div style="background-color: #F2F0F7; min-height: 92vh; padding: 48px 16px; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto; animation: fadeUp .5s ease both;">
        
        <!-- Alerts -->
        @if(session('success'))
            <div style="background-color: rgba(15,169,156,0.1); border: 1px solid #0FA99C; color: #0FA99C; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 13.5px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background-color: rgba(224,85,63,0.1); border: 1px solid #E0553F; color: #E0553F; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 13.5px;">
                {{ session('error') }}
            </div>
        @endif

        <!-- 1. Primary Action: Interactive Questions -->
        <div style="text-align: center; margin-bottom: 36px;">
            <div style="display: inline-block; background-color: #0FA99C; color: white; font-size: 10px; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                Step 1 of 2
            </div>
            <h2 style="font-family: 'Playfair Display', Georgia, serif; font-weight: 700; font-size: clamp(24px, 4vw, 32px); color: #15141C; margin: 0 0 8px;">
                Let’s build your credit game plan
            </h2>
            <p style="font-size: 14.5px; color: #8B879A; max-width: 440px; margin: 0 auto 24px; line-height: 1.5;">
                Answer a few quick questions about your credit and goals.
            </p>
            
            <a href="{{ route('credit-reports.questionnaire') }}" style="display: inline-block; text-align: center; width: 100%; max-width: 360px; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.02em; padding: 16px 32px; border-radius: 8px; cursor: pointer; background: #0FA99C; color: white; border: none; text-decoration: none; box-shadow: 0 4px 14px rgba(15,169,156,0.25); transition: all 0.18s ease;"
               onmouseover="this.style.background='#0B7E74'; this.style.transform='translateY(-1px)';"
               onmouseout="this.style.background='#0FA99C'; this.style.transform='translateY(0)';">
                CONTINUE WITH QUESTIONS →
            </a>
        </div>

        <!-- Visual Separator -->
        <div style="display: flex; align-items: center; margin: 40px 0;">
            <div style="flex: 1; height: 1px; background: #E4E2EB;"></div>
            <span style="font-size: 11px; font-weight: 700; color: #8B879A; padding: 0 16px; text-transform: uppercase; letter-spacing: 0.1em;">or LET ALLY LOOK DEEPER</span>
            <div style="flex: 1; height: 1px; background: #E4E2EB;"></div>
        </div>

        <!-- 2. Deeper Analysis Section -->
        <div style="background: white; border: 1px solid #E4E2EB; border-radius: 16px; padding: 30px 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.01);">
            <div style="text-align: center; margin-bottom: 24px;">
                <p style="font-size: 14.5px; color: #3A3844; line-height: 1.6; margin: 0;">
                    Let Ally review your actual credit report for a more personalized game plan.
                </p>
            </div>

            <!-- File Upload Form (Already have report?) -->
            <form action="{{ route('credit-reports.upload') }}" method="POST" enctype="multipart/form-data" id="upload-form" style="margin-bottom: 28px;">
                @csrf
                <p style="font-size: 12.5px; font-weight: 700; color: #15141C; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
                    Already have a report?
                </p>
                
                <div id="dropzone" style="border: 2px dashed #E4E2EB; border-radius: 12px; padding: 30px 20px; text-align: center; cursor: pointer; background: #F8F7FA; transition: all .15s ease;"
                     onclick="document.getElementById('credit_file').click();"
                     ondragover="event.preventDefault(); this.style.borderColor='#0FA99C'; this.style.background='#F0FBFB';"
                     ondragleave="this.style.borderColor='#E4E2EB'; this.style.background='#F8F7FA';"
                     ondrop="event.preventDefault(); this.style.borderColor='#E4E2EB'; this.style.background='#F8F7FA'; handleFiles(event.dataTransfer.files);">
                    
                    <div style="font-size: 24px; margin-bottom: 8px;">📄</div>
                    <div style="font-weight: 600; font-size: 14px; color: #15141C; margin-bottom: 3px;" id="upload-instruction">
                        Drop your credit report file here, or click to browse
                    </div>
                    <div style="font-size: 11.5px; color: #8B879A;" id="upload-support">
                        Currently supports reports downloaded from IdentityIQ.
                    </div>
                    
                    <input type="file" name="credit_file" id="credit_file" style="display: none;" 
                           accept=".html,.htm,.pdf,.png,.jpg,.jpeg" 
                           onchange="handleFiles(this.files)" required>
                </div>

                <div id="file-list" style="margin-top: 12px; display: none;">
                    <div style="display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid #E4E2EB; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px;">
                        <span id="file-name" style="font-size: 12.5px; color: #3A3844; font-weight: 500;"></span>
                        <button type="button" onclick="clearFile()" style="background: none; border: none; font-size: 11.5px; color: #E0553F; text-decoration: underline; cursor: pointer; padding: 0;">Remove</button>
                    </div>
                    <button type="submit" style="width: 100%; font-family: 'Inter', sans-serif; font-size: 13.5px; font-weight: 700; padding: 12px; border-radius: 8px; cursor: pointer; background: #0FA99C; color: white; border: none; transition: background 0.15s;"
                            onmouseover="this.style.background='#0B7E74';"
                            onmouseout="this.style.background='#0FA99C';">
                        UPLOAD MY REPORT
                    </button>
                </div>
            </form>

            <!-- Affiliate (Don't have report?) -->
            <div style="border-top: 1px solid #E4E2EB; padding-top: 24px; text-align: center;">
                <p style="font-size: 13.5px; color: #3A3844; margin-bottom: 14px; line-height: 1.5;">
                    <strong>Don’t have one?</strong> Get your 3-bureau credit report for $1 so Ally can take a closer look.
                </p>
                <a href="https://member.identityiq.com/securepreferred.aspx?offercode=4312857A" target="_blank" rel="noopener noreferrer" 
                   style="display: inline-block; text-align: center; width: 100%; max-width: 320px; font-family: 'Inter', sans-serif; font-size: 13.5px; font-weight: 600; padding: 12px 24px; border-radius: 8px; cursor: pointer; background: transparent; color: #15141C; border: 1.5px solid #15141C; text-decoration: none; transition: all 0.18s ease;"
                   onmouseover="this.style.background='#15141C'; this.style.color='#FFFFFF';"
                   onmouseout="this.style.background='transparent'; this.style.color='#15141C';">
                    GET MY REPORT FOR $1 →
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            
            // Set file input files if dropped
            const fileInput = document.getElementById('credit_file');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            document.getElementById('file-name').textContent = file.name;
            document.getElementById('dropzone').style.display = 'none';
            document.getElementById('file-list').style.display = 'block';
        }
    }

    function clearFile() {
        document.getElementById('credit_file').value = '';
        document.getElementById('dropzone').style.display = 'block';
        document.getElementById('file-list').style.display = 'none';
    }
</script>

<style>
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
