<!-- resources/views/partials/guidelines-modal.blade.php -->

<div class="modal fade" id="guidelineModal" tabindex="-1" aria-labelledby="guidelineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold" id="guidelineModalLabel">📌 Dispute Letter Guidelines</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ol class="ps-3">
                    <li><strong>View and Edit Your Letter</strong><br>
                        You can view and edit your dispute letter if it has not been marked as "Posted". Click “📄 View Letter” then “✏️ Edit Letter” to make changes.
                    </li>
                    <li class="mt-3"><strong>Finalize the Letter</strong><br>
                        Before sending, ensure your letter contains your real name, address, phone number, and email. Then click “📬 Mark as Final” to finalize.
                    </li>
                    <li class="mt-3"><strong>Download & Send</strong><br>
                        After finalizing, download the letter as PDF and send it via USPS Certified Mail with tracking to the credit bureau.
                    </li>
                    <li class="mt-3"><strong>Follow-up After 15 Days</strong><br>
                        - Our system auto-generates follow-up letters after 15 days if needed.<br>
                        - If not auto-generated, you can manually click <strong>“🔁 Generate Follow-Up”</strong> under the letter view.
                    </li>
                </ol>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
