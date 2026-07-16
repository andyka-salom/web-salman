{{-- resources/views/cermat/partials/risk-matrix-modal.blade.php --}}
<div class="modal fade animated fadeInDown" id="riskMatrixModal" tabindex="-1" role="dialog" aria-labelledby="riskMatrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="riskMatrixModalLabel">Panduan 5x5 Risk Matrix</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered risk-matrix-table">
                        <thead class="text-center align-middle">
                            <tr><th colspan="12" class="header-main fs-5">5 x 5 RISK MATRIX</th></tr>
                            <tr><th colspan="7" class="header-sub">HAZARD EFFECT (SEVERITY)</th><th colspan="5" class="header-sub">PROBABILITY (LIKELIHOOD)</th></tr>
                            <tr><th>People</th><th>Environment</th><th>Financial Impact</th><th>Reputation Impact & Legal</th><th>Asset & Equipment</th><th>Public Notification</th><th>Level</th><th>1<br><small>0% < x < 20%<br>< 10<sup>-6</sup> per year</small></th><th>2<br><small>2% < x < 40%<br>10<sup>-6</sup> to 10<sup>-4</sup> per year</small></th><th>3<br><small>40% < x < 60%<br>10<sup>-4</sup> to 10<sup>-2</sup> per year</small></th><th>4<br><small>60% < x < 80%<br>10<sup>-2</sup> to 1 per year</small></th><th>5<br><small>80% < x < 100%<br>> 1 per year</small></th></tr>
                        </thead>
                        <tbody class="align-middle">
                            <!-- LEVEL 5: Catastrophic -->
                            <tr>
                                <td class="desc-cell"><ul class="list-unstyled"><li>- Multiple Fatalities</li><li>- Outbreak to neighbourhood</li><li>- Agents with the potential to cause multiple fatalities e.g. chemicals with acute toxic effects</li></ul></td>
                                <td class="desc-cell">Very serious, long term environmental impairment of ecosystem function or Oil Spill > 100 barrel</td>
                                <td class="desc-cell">≥80% BTR</td>
                                <td class="desc-cell"><strong>International and National Wide Impact</strong><ul class="list-unstyled"><li>- Potential national & international media coverage affecting the company & parent company (Pertamina Persero)</li><li>- Potential legal suit by regulator & affected community</li><li>- Public outcry to cease operation</li><li>- Potential environmental remediation demanded by regulator</li></ul></td>
                                <td class="desc-cell">Total Loss of Plant or estimated repair cost > US$ 5,000,000</td>
                                <td class="desc-cell">Complete area evacuation</td>
                                <td class="text-center"><b>5</b><br>Catastrophic</td>
                                <td class="risk-level-medium">5</td><td class="risk-level-high">10</td><td class="risk-level-extreme">15</td><td class="risk-level-extreme">20</td><td class="risk-level-extreme">25</td>
                            </tr>
                            <!-- LEVEL 4: Significant -->
                            <tr>
                                <td class="desc-cell"><ul class="list-unstyled"><li>- Single Fatality / Permanent disability / Days Away From Work Cases (LTI)</li><li>- Agents capable of irreversible effects leading to death</li></ul></td>
                                <td class="desc-cell">Serious medium term environmental effect or Oil Spill 15 – 100 barrel</td>
                                <td class="desc-cell">60% - 80% BTR</td>
                                <td class="desc-cell"><strong>Regional impact</strong><ul class="list-unstyled"><li>- Potential regional and/or international media coverage to the company and parent company (Pertamina Persero)</li><li>- Potential legal suit by regulator & affected community</li><li>- Potential environmental remediation demanded by regulator</li></ul></td>
                                <td class="desc-cell">Partial loss of plant; Plant shut down or estimated repair cost US$ 1,000,000 – US$ 5,000,000</td>
                                <td class="desc-cell">Selected areas of evacuation notification</td>
                                <td class="text-center"><b>4</b><br>Significant</td>
                                <td class="risk-level-low">4</td><td class="risk-level-medium">8</td><td class="risk-level-high">12</td><td class="risk-level-extreme">16</td><td class="risk-level-extreme">20</td>
                            </tr>
                            <!-- LEVEL 3: Moderate -->
                            <tr>
                                <td class="desc-cell"><ul class="list-unstyled"><li>- Non Permanent disability / Restricted Work Day Cases</li><li>- Agents capable of irreversible effects without loss of life but with serious disability and prolonged hospitalization</li></ul></td>
                                <td class="desc-cell">Moderate, short term effect but not affecting ecosystem function or Oil spill 5-15 bbls</td>
                                <td class="desc-cell">40% - 60% BTR</td>
                                <td class="desc-cell"><strong>Local (city impact)</strong><ul class="list-unstyled"><li>- Potential local press exposures</li><li>- Potential legal claim by affected victims</li><li>- Potential environmental remediations needed</li></ul></td>
                                <td class="desc-cell">Plant partly down or estimated repair cost $100,000 - $1,000,000</td>
                                <td class="desc-cell">Shelter in place notification</td>
                                <td class="text-center"><b>3</b><br>Moderate</td>
                                <td class="risk-level-very-low">3</td><td class="risk-level-medium">6</td><td class="risk-level-medium">9</td><td class="risk-level-high">12</td><td class="risk-level-extreme">15</td>
                            </tr>
                            <!-- LEVEL 2: Minor -->
                            <tr>
                                <td class="desc-cell"><ul class="list-unstyled"><li>- Medical treatment case</li><li>- Agents capable of minor health effects which are reversible (no hospitalization)</li></ul></td>
                                <td class="desc-cell">Minor effect on biological or physical environment or Oil spill 1-5 bbls</td>
                                <td class="desc-cell">20% - 40% BTR</td>
                                <td class="desc-cell"><strong>Internal Impact</strong><ul class="list-unstyled"><li>- Potential press exposures</li><li>- Query by regulator</li></ul></td>
                                <td class="desc-cell">Possible brief disruption of the process or estimated repair cost $10,000 - $100,000</td>
                                <td class="desc-cell">Local (selected phone/leaflet notice)</td>
                                <td class="text-center"><b>2</b><br>Minor</td>
                                <td class="risk-level-very-low">2</td><td class="risk-level-low">4</td><td class="risk-level-medium">6</td><td class="risk-level-medium">8</td><td class="risk-level-high">10</td>
                            </tr>
                            <!-- LEVEL 1: Insignificant -->
                            <tr>
                                <td class="desc-cell"><ul class="list-unstyled"><li>- First Aid Cases</li><li>- No effect on work performance</li></ul></td>
                                <td class="desc-cell">Limited damage to minimal area of low significance or Oil Spill < 1 bbls</td>
                                <td class="desc-cell">≤20% BTR</td>
                                <td class="desc-cell"><strong>No Reputation Impact</strong><ul class="list-unstyled"><li>- No media concern</li></ul></td>
                                <td class="desc-cell">No disruption to process or estimated repair cost <$10,000</td>
                                <td class="desc-cell">No communication to public</td>
                                <td class="text-center"><b>1</b><br>Insignificant</td>
                                <td class="risk-level-very-low">1</td><td class="risk-level-very-low">2</td><td class="risk-level-low">3</td><td class="risk-level-low">4</td><td class="risk-level-medium">5</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-dark" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
