{{-- resources/views/cermat/partials/risk_matrix_modal.blade.php --}}

<div class="modal fade" id="riskMatrixModal" tabindex="-1" aria-labelledby="riskMatrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="riskMatrixModalLabel">
                    <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>
                    Panduan 5x5 Risk Matrix
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered risk-matrix-table mb-0">
                        <thead class="text-center align-middle">
                            <tr>
                                <th colspan="12" class="header-main fs-5">5 x 5 RISK MATRIX</th>
                            </tr>
                            <tr>
                                <th colspan="7" class="header-sub">HAZARD EFFECT (SEVERITY)</th>
                                <th colspan="5" class="header-sub">PROBABILITY (LIKELIHOOD)</th>
                            </tr>
                            <tr>
                                <th>People</th>
                                <th>Environment</th>
                                <th>Financial Impact</th>
                                <th>Reputation Impact & Legal</th>
                                <th>Asset & Equipment</th>
                                <th>Public Notification</th>
                                <th>Level</th>
                                <th>1<br><small>0% < x < 20%</small></th>
                                <th>2<br><small>20% < x < 40%</small></th>
                                <th>3<br><small>40% < x < 60%</small></th>
                                <th>4<br><small>60% < x < 80%</small></th>
                                <th>5<br><small>80% < x < 100%</small></th>
                            </tr>
                        </thead>
                        <tbody class="align-middle small">
                            <!-- Level 5: Catastrophic -->
                            <tr>
                                <td>Multiple Fatalities / Outbreak to neighbourhood</td>
                                <td>Very serious, long term environmental impairment</td>
                                <td>≥80% BTR</td>
                                <td>International & National Wide Impact</td>
                                <td>Total Loss of Plant (> US$ 5M)</td>
                                <td>Complete area evacuation</td>
                                <td class="text-center fw-bold">5<br>Catastrophic</td>
                                <td class="risk-level-medium">5</td>
                                <td class="risk-level-high">10</td>
                                <td class="risk-level-extreme">15</td>
                                <td class="risk-level-extreme">20</td>
                                <td class="risk-level-extreme">25</td>
                            </tr>
                            <!-- Level 4: Significant -->
                            <tr>
                                <td>Single Fatality / Permanent disability / LTI</td>
                                <td>Serious medium term environmental effect</td>
                                <td>60% - 80% BTR</td>
                                <td>Regional impact</td>
                                <td>Partial loss (US$ 1M - 5M)</td>
                                <td>Selected areas evacuation</td>
                                <td class="text-center fw-bold">4<br>Significant</td>
                                <td class="risk-level-low">4</td>
                                <td class="risk-level-medium">8</td>
                                <td class="risk-level-high">12</td>
                                <td class="risk-level-extreme">16</td>
                                <td class="risk-level-extreme">20</td>
                            </tr>
                            <!-- Level 3: Moderate -->
                            <tr>
                                <td>Non Permanent disability / Restricted Work</td>
                                <td>Moderate, short term effect</td>
                                <td>40% - 60% BTR</td>
                                <td>Local (city impact)</td>
                                <td>Plant partly down (US$ 100K - 1M)</td>
                                <td>Shelter in place</td>
                                <td class="text-center fw-bold">3<br>Moderate</td>
                                <td class="risk-level-very-low">3</td>
                                <td class="risk-level-medium">6</td>
                                <td class="risk-level-medium">9</td>
                                <td class="risk-level-high">12</td>
                                <td class="risk-level-extreme">15</td>
                            </tr>
                            <!-- Level 2: Minor -->
                            <tr>
                                <td>Medical treatment case</td>
                                <td>Minor effect on environment</td>
                                <td>20% - 40% BTR</td>
                                <td>Internal Impact</td>
                                <td>Brief disruption (US$ 10K - 100K)</td>
                                <td>Local notice</td>
                                <td class="text-center fw-bold">2<br>Minor</td>
                                <td class="risk-level-very-low">2</td>
                                <td class="risk-level-low">4</td>
                                <td class="risk-level-medium">6</td>
                                <td class="risk-level-medium">8</td>
                                <td class="risk-level-high">10</td>
                            </tr>
                            <!-- Level 1: Insignificant -->
                            <tr>
                                <td>First Aid Cases / No effect</td>
                                <td>Limited damage minimal area</td>
                                <td>≤20% BTR</td>
                                <td>No Reputation Impact</td>
                                <td>No disruption (< US$ 10K)</td>
                                <td>No communication</td>
                                <td class="text-center fw-bold">1<br>Insignificant</td>
                                <td class="risk-level-very-low">1</td>
                                <td class="risk-level-very-low">2</td>
                                <td class="risk-level-low">3</td>
                                <td class="risk-level-low">4</td>
                                <td class="risk-level-medium">5</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Keterangan Level Risiko:</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success" style="width: 80px;">1-3</span>
                                <span>Very Low - Dapat diterima dengan kontrol existing</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" style="width: 80px; background-color: #90EE90; color: #343a40;">4-6</span>
                                <span>Low - Review kontrol secara berkala</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning text-dark" style="width: 80px;">8-12</span>
                                <span>Medium - Butuh action plan untuk menurunkan risiko</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" style="width: 80px; background-color: #fd7e14; color: white;">15-16</span>
                                <span>High - Perlu action segera dan eskalasi ke manajemen</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger" style="width: 80px;">20-25</span>
                                <span>Extreme - Stop aktivitas, butuh kontrol segera & persetujuan eksekutif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
